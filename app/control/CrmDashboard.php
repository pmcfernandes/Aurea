<?php
declare(strict_types=1);

namespace App\Control;

use App\Model\Customer;
use App\Model\CustomerActivity;
use App\Model\CustomerDocument;
use App\Model\CustomerOpportunity;
use Aurea\Core\TAssets;
use Aurea\Core\TPage;
use Aurea\Database\TTransaction;
use Aurea\Mvc\TView;

/** Painel principal com os indicadores operacionais do CRM. */
class CrmDashboard extends TPage
{
    public function __construct()
    {
        parent::__construct();
        TAssets::addCss('/assets/dashboard.css');
    }

    public function onShow(array $param = []): void
    {
        $data = [
            'customers' => 0,
            'activeCustomers' => 0,
            'pipeline' => 0.0,
            'plannedActivities' => 0,
            'validDocuments' => 0,
            'recentCustomers' => [],
            'recentActivities' => [],
        ];

        try {
            TTransaction::open('app');
            $data['customers'] = count(Customer::all());
            $data['activeCustomers'] = Customer::where('status', '=', 'customer')->count();
            $openOpportunities = CustomerOpportunity::where('status', '=', 'open')->get();
            $data['pipeline'] = array_sum(array_map(static fn(CustomerOpportunity $opportunity): float => (float) $opportunity->amount, $openOpportunities));
            $data['plannedActivities'] = CustomerActivity::where('status', '=', 'planned')->count();
            $data['validDocuments'] = CustomerDocument::where('status', '=', 'valid')->count();
            $data['recentCustomers'] = array_map(
                static fn(Customer $customer): array => $customer->toArray(),
                Customer::where('id', '>', 0)->orderBy('created_at', 'DESC')->take(5)->get()
            );
            $data['recentActivities'] = array_map(static function (CustomerActivity $activity): array {
                $customer = $activity->customer();
                return [
                    'subject' => $activity->subject,
                    'type' => $activity->type,
                    'scheduled_at' => $activity->scheduled_at,
                    'customer_name' => $customer?->name ?? 'Cliente removido',
                ];
            }, CustomerActivity::where('id', '>', 0)->orderBy('created_at', 'DESC')->take(5)->get());
            TTransaction::close();
        } catch (\Throwable) {
            TTransaction::rollback();
        }

        $this->addHtml((new TView('dashboard/index'))->set('dashboard', $data)->fetch());
    }
}
