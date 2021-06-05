<x-app-layout :title="$title">
    <section class="section">
        <div class="section-header">
            <div class="section-header-back">
                <a href="{{ route('report.index') }}" class="btn btn-icon"><i class="fas fa-arrow-left"></i></a>
            </div>
            <h1>{{ $title }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="#">Ekstra</a></div>
                <div class="breadcrumb-item"><a href="{{ route('report.index') }}">Laporan</a></div>
                <div class="breadcrumb-item">Keuangan</div>
            </div>
        </div>

        <div class="section-body">
            <div>
                <h2 class="section-title">Pemasukan</h2>
                <p class="section-lead">Semua data pemasukan ini belum dikurangi dengan pengeluaran.</p>
            </div>
            <div class="row">
                @foreach ($stats as $key => $item)
                    <x-percentage :result="$stats[$key]" />
                @endforeach
            </div>

            <div class="row">
                <div class="col-lg-8 col-md-12 col-12 col-sm-12">
                    <livewire:finance-income />
                </div>
                <div class="col-lg-4 col-md-12 col-12 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Pemasukan Berdasarkan Tagihan</h4>
                        </div>
                        <div class="card-body" style="height: 489px!important;overflow-y:scroll;">
                            <x-widget type="primary" title="Total Keseluruhan" class="card-list-icon"
                                icon="fas fa-dollar-sign"
                                :value="idr(array_sum($bills->pluck('payments_sum_pay')->toArray()))" />
                            @foreach ($bills as $bill)
                                <x-widget type="primary" :title="$bill->name" class="card-list-icon"
                                    icon="fas fa-money-bill-alt" :value="idr($bill->payments_sum_pay)" />
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="section-body">
            <h2 class="section-title">Pengeluaran</h2>
            <p class="section-lead">Semua data pengeluaran.</p>
        </div>
    </section>
</x-app-layout>