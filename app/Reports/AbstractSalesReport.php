<?php

namespace App\Reports;

use Illuminate\Support\Facades\DB;

abstract class AbstractSalesReport
{
    public function __construct(
        protected \Carbon\CarbonInterface $start,
        protected \Carbon\CarbonInterface $end,
        protected int $top = 10,
        protected array $statuses = ['Shipped','Arrived','Completed','Paid','Processing'], // widened
        protected string $dateCol = 'o.created_at' // << use the real column here
    ) {}

    /** Return a plain SQL string for the time bucket (e.g. "DATE(o.created_at)") */
    abstract protected function bucketExpr(): string;

    /** TEMPLATE METHOD: fixed algorithm */
    final public function build(): array
    {
        $bucket = $this->bucketExpr();

        // KPIs
        $kpi = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereBetween($this->dateCol, [$this->start, $this->end])
            ->whereIn('o.status', $this->statuses)
            ->selectRaw('SUM(oi.quantity * oi.unit_price) as revenue')
            ->selectRaw('COUNT(DISTINCT o.id) as orders')
            ->selectRaw('SUM(oi.quantity) as items')
            ->first();

        $avgOrder = ($kpi && ($kpi->orders ?? 0) > 0) ? round($kpi->revenue / $kpi->orders, 2) : 0.0;

        // Series
        $series = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereBetween($this->dateCol, [$this->start, $this->end])
            ->whereIn('o.status', $this->statuses)
            ->selectRaw("$bucket as bucket")
            ->selectRaw('SUM(oi.quantity * oi.unit_price) as revenue')
            ->selectRaw('SUM(oi.quantity) as items')
            ->selectRaw('COUNT(DISTINCT o.id) as orders')
            ->groupByRaw($bucket)
            ->orderByRaw($bucket)
            ->get();

        // Top books
        $topBooks = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->join('books as b', 'b.id', '=', 'oi.book_id')
            ->whereBetween($this->dateCol, [$this->start, $this->end])
            ->whereIn('o.status', $this->statuses)
            ->select('b.id','b.title')
            ->selectRaw('SUM(oi.quantity) as qty')
            ->selectRaw('SUM(oi.quantity * oi.unit_price) as revenue')
            ->groupBy('b.id','b.title')
            ->orderByDesc('revenue')
            ->limit($this->top)
            ->get();

        // By Category
        $byCategory = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('book_category as bc', 'bc.book_id', '=', 'oi.book_id')
            ->leftJoin('categories as c', 'c.id', '=', 'bc.category_id')
            ->whereBetween($this->dateCol, [$this->start, $this->end])
            ->whereIn('o.status', $this->statuses)
            ->selectRaw('COALESCE(c.id, 0) as category_id')
            ->selectRaw("COALESCE(c.name, 'Uncategorized') as category_name")
            ->selectRaw('SUM(oi.quantity) as qty')
            ->selectRaw('SUM(oi.quantity * oi.unit_price) as revenue')
            ->groupBy('c.id','c.name')
            ->orderByDesc('revenue')
            ->limit(50)
            ->get();

        return compact('kpi','avgOrder','series','topBooks','byCategory');
    }
}
