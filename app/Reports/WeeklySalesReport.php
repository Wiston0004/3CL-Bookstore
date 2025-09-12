<?php

namespace App\Reports;

class WeeklySalesReport extends AbstractSalesReport
{
    protected function bucketExpr(): string
    {
        return "YEARWEEK($this->dateCol, 1)";
    }
}
