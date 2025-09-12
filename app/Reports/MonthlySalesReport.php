<?php

namespace App\Reports;

class MonthlySalesReport extends AbstractSalesReport
{
    protected function bucketExpr(): string
    {
        return "DATE_FORMAT($this->dateCol, '%Y-%m')";
    }
}
