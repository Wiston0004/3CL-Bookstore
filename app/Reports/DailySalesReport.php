<?php

namespace App\Reports;

class DailySalesReport extends AbstractSalesReport
{
    protected function bucketExpr(): string
    {
        return "DATE($this->dateCol)";
    }
}
