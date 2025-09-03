@extends('layouts.app')
@section('title','Sales Report')

@section('header')
  <h2 class="font-semibold text-xl text-gray-200">📈 Sales Report</h2>
@endsection

@section('content')
<div class="grid" style="gap:18px">

  {{-- Filters --}}
  <div class="card">
        <div class="row" style="justify-content:flex-end">
    <a href="{{ route('manager.dashboard') }}" class="btn">← Back to Dashboard</a>
    </div>
    <form method="GET" class="grid grid-3" style="gap:12px">
      <div>
        <label>Start</label>
        <input type="date" name="start" value="{{ $start->toDateString() }}" class="input">
      </div>
      <div>
        <label>End</label>
        <input type="date" name="end" value="{{ $end->toDateString() }}" class="input">
      </div>
      <div>
        <label>Group</label>
        <select name="group" class="input">
          <option value="day"   {{ $group==='day'?'selected':'' }}>Day</option>
          <option value="week"  {{ $group==='week'?'selected':'' }}>Week</option>
          <option value="month" {{ $group==='month'?'selected':'' }}>Month</option>
        </select>
      </div>
      <div>
        <label>Top N Books</label>
        <input type="number" min="1" name="top" value="{{ $top }}" class="input">
      </div>
      <div class="row right mt">
        <a class="btn" href="{{ route('manager.reports.sales') }}">Reset</a>
        <button class="btn primary">Apply</button>
      </div>
    </form>
  </div>

  {{-- KPI Cards --}}
  <div class="grid grid-4" style="grid-template-columns:repeat(4,1fr);gap:16px">
    <div class="card">
      <div class="muted">Revenue</div>
      <div style="font-size:1.6rem;font-weight:700;margin-top:6px">RM {{ number_format($kpi->revenue ?? 0, 2) }}</div>
    </div>
    <div class="card">
      <div class="muted">Orders</div>
      <div style="font-size:1.6rem;font-weight:700;margin-top:6px">{{ number_format($kpi->orders ?? 0) }}</div>
    </div>
    <div class="card">
      <div class="muted">Items Sold</div>
      <div style="font-size:1.6rem;font-weight:700;margin-top:6px">{{ number_format($kpi->items ?? 0) }}</div>
    </div>
    <div class="card">
      <div class="muted">Avg Order</div>
      <div style="font-size:1.6rem;font-weight:700;margin-top:6px">RM {{ number_format($avgOrder, 2) }}</div>
    </div>
  </div>

  {{-- Charts Row --}}
  <div class="grid grid-2" style="gap:16px">
    <div class="card">
      <div class="row" style="justify-content:space-between;margin-bottom:8px">
        <div style="font-weight:600">Revenue over time ({{ ucfirst($group) }})</div>
      </div>
      <div style="height:280px"><canvas id="salesTrendChart"></canvas></div>
      @if($series->isEmpty())
        <p class="muted mt">No data for selected range.</p>
      @endif>

      <details class="mt">
        <summary class="muted">Show table</summary>
        <div class="mt" style="overflow:auto">
          <table class="table">
            <thead>
              <tr>
                <th>Bucket</th>
                <th class="right">Revenue (RM)</th>
                <th class="right">Orders</th>
                <th class="right">Items</th>
              </tr>
            </thead>
            <tbody>
              @foreach($series as $r)
                <tr>
                  <td>{{ $r->bucket }}</td>
                  <td style="text-align:right">{{ number_format($r->revenue,2) }}</td>
                  <td style="text-align:right">{{ number_format($r->orders) }}</td>
                  <td style="text-align:right">{{ number_format($r->items) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </details>
    </div>

    <div class="card">
      <div class="row" style="justify-content:space-between;margin-bottom:8px">
        <div style="font-weight:600">Top {{ $top }} books (by revenue)</div>
      </div>
      <div style="height:280px"><canvas id="topBooksChart"></canvas></div>
      @if($topBooks->isEmpty())
        <p class="muted mt">No top items in this range.</p>
      @endif>

      <details class="mt">
        <summary class="muted">Show table</summary>
        <div class="mt" style="overflow:auto">
          <table class="table">
            <thead><tr><th>Book</th><th class="right">Qty</th><th class="right">Revenue (RM)</th></tr></thead>
            <tbody>
              @foreach($topBooks as $b)
                <tr>
                  <td>#{{ $b->id }} — {{ $b->title }}</td>
                  <td style="text-align:right">{{ number_format($b->qty) }}</td>
                  <td style="text-align:right">{{ number_format($b->revenue,2) }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </details>
    </div>
  </div>

  {{-- Category Donut --}}
  <div class="card">
    <div style="font-weight:600;margin-bottom:8px">Sales by category</div>
    <div class="grid grid-2" style="gap:16px;align-items:center">
      <div style="height:320px"><canvas id="categoryChart"></canvas></div>
      <div>
        <p class="muted">Revenue share by category. Use this to spot strong performers and gaps in assortment.</p>
        <ul id="categoryLegend" style="list-style:none;padding:0;margin-top:12px"></ul>
      </div>
    </div>
    @if($byCategory->isEmpty())
      <p class="muted mt">No category data.</p>
    @endif>
  </div>

</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  // pull theme colors from your CSS variables
  const css = getComputedStyle(document.documentElement);
  const cBrand   = css.getPropertyValue('--brand').trim() || '#4f46e5';
  const cBrand2  = css.getPropertyValue('--brand-2').trim() || '#06b6d4';
  const cText    = css.getPropertyValue('--text').trim() || '#e5e7eb';
  const cMuted   = css.getPropertyValue('--muted').trim() || '#9aa4c2';
  const cCard    = css.getPropertyValue('--card').trim() || '#121836';

  // shared Chart.js defaults for dark theme
  Chart.defaults.color = cMuted;
  Chart.defaults.borderColor = 'rgba(148,163,184,.15)';

  const toRM = v => 'RM ' + Number(v ?? 0).toLocaleString(undefined, {minimumFractionDigits:2, maximumFractionDigits:2});
  const palette = [cBrand, cBrand2, '#22c55e', '#f59e0b', '#ef4444', '#0ea5e9', '#8b5cf6', '#14b8a6', '#f97316', '#84cc16'];

  // backend data
  const series     = @json($series);
  const topBooks   = @json($topBooks);
  const categories = @json($byCategory);

  // ---- Revenue over time (line)
  try {
    const el = document.getElementById('salesTrendChart');
    if (el && series.length) {
      const labels  = series.map(r => r.bucket);
      const revenue = series.map(r => Number(r.revenue || 0));
      const orders  = series.map(r => Number(r.orders || 0));

      const ctx = el.getContext('2d');
      // gradient for area fill
      const grad = ctx.createLinearGradient(0,0,0,280);
      grad.addColorStop(0, cBrand + 'ee');
      grad.addColorStop(1, cBrand + '11');

      new Chart(ctx, {
        type: 'line',
        data: {
          labels,
          datasets: [
            {
              label: 'Revenue',
              data: revenue,
              borderColor: cBrand,
              backgroundColor: grad,
              tension: 0.35,
              fill: true,
              borderWidth: 2,
              pointRadius: 2
            },
            {
              label: 'Orders',
              data: orders,
              borderColor: cBrand2,
              borderDash: [6,4],
              tension: 0.35,
              yAxisID: 'y1',
              pointRadius: 2
            }
          ]
        },
        options: {
          maintainAspectRatio: false,
          plugins: {
            legend: { labels: { color: cText } },
            tooltip: {
              mode: 'index',
              intersect: false,
              callbacks: {
                label: (ctx) => ctx.dataset.label === 'Revenue'
                  ? `${ctx.dataset.label}: ${toRM(ctx.parsed.y)}`
                  : `${ctx.dataset.label}: ${ctx.parsed.y.toLocaleString()}`
              }
            }
          },
          scales: {
            x: { ticks: { color: cMuted } },
            y: { ticks: { callback: v => toRM(v), color: cMuted } },
            y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { color: cMuted } }
          }
        }
      });
    }
  } catch(e){ console.error(e); }

  // ---- Top books (horizontal bar)
  try {
    const el = document.getElementById('topBooksChart');
    if (el && topBooks.length) {
      const labels  = topBooks.map(b => b.title);
      const revenue = topBooks.map(b => Number(b.revenue || 0));
      new Chart(el, {
        type: 'bar',
        data: {
          labels,
          datasets: [{ label: 'Revenue', data: revenue, backgroundColor: cBrand, borderWidth: 0 }]
        },
        options: {
          indexAxis: 'y',
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: (ctx) => toRM(ctx.parsed.x) } }
          },
          scales: {
            x: { beginAtZero: true, ticks: { callback: v => toRM(v), color: cMuted } },
            y: { ticks: { color: cMuted } }
          }
        }
      });
    }
  } catch(e){ console.error(e); }

  // ---- Category donut
  try {
    const el = document.getElementById('categoryChart');
    if (el && categories.length) {
      const labels  = categories.map(c => c.category_name || 'Uncategorized');
      const revenue = categories.map(c => Number(c.revenue || 0));
      const colors  = labels.map((_,i)=> palette[i % palette.length]);

      const chart = new Chart(el, {
        type: 'doughnut',
        data: { labels, datasets: [{ data: revenue, backgroundColor: colors, borderWidth: 0 }] },
        options: {
          maintainAspectRatio: false,
          cutout: '60%',
          plugins: { legend: { display: false } }
        }
      });

      // custom legend
      const ul = document.getElementById('categoryLegend');
      if (ul) {
        ul.innerHTML = labels.map((label,i)=> `
          <li class="row" style="gap:8px">
            <span style="width:10px;height:10px;background:${colors[i]};border-radius:2px;display:inline-block"></span>
            <span>${label}</span>
            <span class="right muted">${toRM(revenue[i])}</span>
          </li>
        `).join('');
      }
    }
  } catch(e){ console.error(e); }
})();
</script>
@endsection
