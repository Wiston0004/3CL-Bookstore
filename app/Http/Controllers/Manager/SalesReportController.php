<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Reports\AbstractSalesReport;
use Carbon\CarbonImmutable;

class SalesReportController extends Controller
{
    public function index(Request $req)
{
    $tz    = 'Asia/Kuala_Lumpur';

    $start = $req->filled('start')
        ? \Carbon\CarbonImmutable::parse($req->get('start'), $tz)->startOfDay()
        : \Carbon\CarbonImmutable::now($tz)->subDays(30)->startOfDay();

    $end = $req->filled('end')
        ? \Carbon\CarbonImmutable::parse($req->get('end'), $tz)->endOfDay()
        : \Carbon\CarbonImmutable::now($tz)->endOfDay();

    $group = $req->get('group','day'); // day|week|month
    $top   = max(1, (int) $req->get('top', 10));

    // <<< PICK YOUR DATE COLUMN HERE
    // use one of: 'o.created_at'  | 'o.paid_at' | 'o.order_date'
    $dateCol = $req->get('date_col', 'o.created_at');

    // widen statuses so newly paid orders show up; tweak as needed
    $statuses = $req->has('status')
        ? array_values((array)$req->get('status'))
        : ['Paid','Processing','Shipped','Arrived','Completed'];

    // Build bucket using that same column
    $bucket = match ($group) {
        'week'  => "DATE_FORMAT($dateCol, '%x-W%v')",
        'month' => "DATE_FORMAT($dateCol, '%Y-%m')",
        default => "DATE($dateCol)",
    };

    // Template Method: override only the hook; pass statuses & date column to base
    $report = new class($start, $end, $top, $statuses, $dateCol, $bucket) extends \App\Reports\AbstractSalesReport {
        public function __construct($start,$end,$top,$statuses,$dateCol,private string $bucket) {
            parent::__construct($start,$end,$top,$statuses,$dateCol);
        }
        protected function bucketExpr(): string { return $this->bucket; }
    };

    $data = $report->build();

    return view('manager.reports.sales', [
        'start'=>$start,'end'=>$end,'group'=>$group,'top'=>$top,
        'kpi'=>$data['kpi'],'avgOrder'=>$data['avgOrder'],
        'series'=>$data['series'],'topBooks'=>$data['topBooks'],
        'byCategory'=>$data['byCategory'],
    ]);
}

}
