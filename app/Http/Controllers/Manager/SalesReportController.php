<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\CarbonImmutable;
use App\Reports\DailySalesReport;
use App\Reports\WeeklySalesReport;
use App\Reports\MonthlySalesReport;

class SalesReportController extends Controller
{
    public function index(Request $req)
    {
        $tz = 'Asia/Kuala_Lumpur';

        $start = $req->filled('start')
            ? CarbonImmutable::parse($req->get('start'), $tz)->startOfDay()
            : CarbonImmutable::now($tz)->subDays(30)->startOfDay();

        $end = $req->filled('end')
            ? CarbonImmutable::parse($req->get('end'), $tz)->endOfDay()
            : CarbonImmutable::now($tz)->endOfDay();

        $group   = $req->get('group', 'day'); // day|week|month
        $top     = max(1, (int) $req->get('top', 10));
        $dateCol = $req->get('date_col', 'o.created_at');

        $statuses = $req->has('status')
            ? array_values((array)$req->get('status'))
            : ['Paid','Processing','Shipped','Arrived','Completed'];

        // Pick the right report class
        $report = match ($group) {
            'week'  => new WeeklySalesReport($start, $end, $top, $statuses, $dateCol),
            'month' => new MonthlySalesReport($start, $end, $top, $statuses, $dateCol),
            default => new DailySalesReport($start, $end, $top, $statuses, $dateCol),
        };

        $data = $report->build();

        return view('manager.reports.sales', [
            'start'      => $start,
            'end'        => $end,
            'group'      => $group,
            'top'        => $top,
            'kpi'        => $data['kpi'],
            'avgOrder'   => $data['avgOrder'],
            'series'     => $data['series'],
            'topBooks'   => $data['topBooks'],
            'byCategory' => $data['byCategory'],
        ]);
    }
}
