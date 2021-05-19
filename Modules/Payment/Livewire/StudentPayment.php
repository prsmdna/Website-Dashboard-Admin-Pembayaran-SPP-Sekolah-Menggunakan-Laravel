<?php

namespace Modules\Payment\Livewire;

use Livewire\Component;
use App\Datatables\Traits\Notify;
use Modules\Master\Entities\Bill;
use Illuminate\Support\Facades\DB;
use Modules\Master\Entities\Student;
use Modules\Payment\Entities\Payment;
use Modules\Master\Entities\SchoolYear;
use Modules\Payment\Http\Requests\PaymentRequest;

class StudentPayment extends Component
{
    use Notify;

    /** @var object get data object from controller */
    public $bills;
    public $years;
    public $students;

    /** @var string */
    public $bill = null;
    public $year = null;
    public $student = null;
    public $billResult = null;

    /** @var array */
    public $evens = [];
    public $payments = [];
    public $totalPayment = [];

    /** @var attributes string */
    public $pay;
    public $month;
    public $change;
    public $pay_date;

    /** @var boolean */
    public $paymentState = false;

    public function mount()
    {
        $this->pay_date = date('Y-m-d');
    }

    public function resetValue()
    {
        $this->pay = null;
        $this->change = null;
    }

    public function search()
    {
        $this->billResult = Bill::query()->where('id', $this->bill)->first();

        if ($this->billResult->monthly) {
            $rawQuery = 'MONTH(month) as month, `change`, `pay`, `pay_date`';

            $payments = DB::table('payments')
                ->select(DB::raw($rawQuery))
                ->where('year_id', $this->year)
                ->where('bill_id', $this->bill)
                ->where('student_id', $this->student)
                ->groupBy('id')
                ->orderBy('month', 'asc')
                ->get()
                ->toArray();

            $results = [];
            foreach ($payments as $p) {
                $results[$p->month][] = (array)$p;
            }

            $this->payments = $results;
        }
    }

    public function pay($month)
    {
        $this->totalPayment = [];
        if (isset($this->payments[$month])) {
            foreach ($this->payments[$month] as $value) {
                $this->totalPayment[] = $value['pay'];
            }

            $this->paymentState = ($this->billResult->nominal - array_sum($this->totalPayment)) <= 0 ? true : false;
        } else {
            $this->paymentState = false;
        }

        $this->month = create_date($month);
        $this->emit('pay');
    }

    public function updatedPay()
    {
        $nominal = $this->billResult->nominal;

        if (!is_null($this->pay) && is_numeric($this->pay)) {
            $payed = $nominal - array_sum($this->totalPayment);
            if ($payed != 0 && $this->pay > $payed) {
                $this->change = $this->pay - $payed;
            }
        }

        $this->change = 0;
    }

    public function onPay()
    {
        $request = new PaymentRequest();
        $validated = $this->validate($request->rules(), [], $request->attributes());

        $payment = array_merge($validated, [
            'year_id' => $this->year,
            'bill_id' => $this->bill,
            'student_id' => $this->student,
            'month' => $this->month,
            'change' => $this->change,
            'pay' => abs($validated['pay'] - $this->change)
        ]);

        Payment::create($payment);
        $this->resetValue();
        $this->search();
        return $this->success('Berhasil!', 'Pembayaran telah dilakukan.');
    }

    public function render()
    {
        return view('payment::livewire.payment');
    }
}