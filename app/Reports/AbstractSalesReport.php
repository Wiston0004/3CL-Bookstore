<?php

namespace App\Reports;

use Illuminate\Support\Facades\DB;

abstract class AbstractSalesReport
{
    public function __construct(
        protected \Carbon\CarbonInterface $start,
        protected \Carbon\CarbonInterface $end,
        protected int $top = 10,
        protected array $statuses = ['Shipped','Arrived','Completed','Paid','Processing'],
        protected string $dateCol = 'o.created_at'
    ) {}

    /** Subclasses define how to group the date column (day/week/month) */
    abstract protected function bucketExpr(): string;

    /** TEMPLATE METHOD – fixed reporting algorithm */
    final public function build(): array
    {
        $bucket     = $this->bucketExpr();
        $kpi        = $this->computeKPI();
        $avgOrder   = $this->computeAverageOrder($kpi);
        $series     = $this->buildSeries($bucket);
        $topBooks   = $this->buildTopBooks();
        $byCategory = $this->buildByCategory();

        return compact('kpi','avgOrder','series','topBooks','byCategory');
    }

    protected function computeKPI()
    {
        return DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->whereBetween($this->dateCol, [$this->start, $this->end])
            ->whereIn('o.status', $this->statuses)
            ->selectRaw('SUM(oi.quantity * oi.unit_price) as revenue')
            ->selectRaw('COUNT(DISTINCT o.id) as orders')
            ->selectRaw('SUM(oi.quantity) as items')
            ->first();
    }

    protected function computeAverageOrder($kpi): float
    {
        return ($kpi && ($kpi->orders ?? 0) > 0)
            ? round($kpi->revenue / $kpi->orders, 2)
            : 0.0;
    }

    protected function buildSeries(string $bucket)
    {
        return DB::table('order_items as oi')
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
    }

    protected function buildTopBooks()
    {
        return DB::table('order_items as oi')
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
    }

    protected function buildByCategory()
    {
        return DB::table('order_items as oi')
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
    }
}
