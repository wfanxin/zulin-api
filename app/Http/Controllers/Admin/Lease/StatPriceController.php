<?php

namespace App\Http\Controllers\Admin\Lease;

use App\Http\Controllers\Admin\Controller;
use App\Http\Traits\FormatTrait;
use App\Model\Admin\Company;
use App\Model\Admin\Notice;
use App\Model\Admin\StatPrice;
use App\Model\Admin\User;
use Illuminate\Http\Request;

/**
 * @name 金额统计
 * Class NoticeController
 * @package App\Http\Controllers\Admin\Lease
 * @PermissionWhiteList
 *
 * @Resource("stat_prices")
 */
class StatPriceController extends Controller
{
    use FormatTrait;

    /**
     * @name 统计数据
     * @Get("/lv/lease/statPrice/getData")
     * @Version("v1")
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     **/
    public function getData(Request $request, StatPrice $mStatPrice, Company $mCompany)
    {
        $params = $request->all();
        $params['userId'] = $request->userId;

        $chartData = $property_chartData = [
            'columns' => ['year', 'price'],
            'rows' => [
                ['year' => date('Y') - 1 . '年', 'price' => 0],
                ['year' => date('Y') + 0 . '年', 'price' => 0],
                ['year' => date('Y') + 1 . '年', 'price' => 0],
            ]
        ];

        $company_list = $mCompany->get(['id', 'company_name']);
        $company_list = $this->dbResult($company_list);

        $company_id = $params['company_id'] ?? '';
        if (empty($company_id)) {
            return $this->jsonAdminResult([
                'chartData' => $chartData,
                'property_chartData' => $property_chartData,
                'company_list' => $company_list
            ]);
        }

        $where = [];
        $where[] = ['company_id', $company_id];
        $where[] = [function ($query) {
            $query->whereIn('year', [date('Y') - 1, date('Y') + 0, date('Y') + 1]);
        }];

        $stat = $mStatPrice
            ->selectRaw('year, type, SUM(price) as price')
            ->where($where)
            ->groupBy('type', 'year')
            ->get();
        $stat = $this->dbResult($stat);

        foreach ($stat as $value) {
            if ($value['type'] == 1) {
                foreach ($chartData['rows'] as $k => $v) {
                    if ($v['year'] == $value['year'] . '年') {
                        $chartData['rows'][$k]['price'] += $value['price'];
                    }
                }
            } else if ($value['type'] == 2) {
                foreach ($property_chartData['rows'] as $k => $v) {
                    if ($v['year'] == $value['year'] . '年') {
                        $property_chartData['rows'][$k]['price'] += $value['price'];
                    }
                }
            }
        }

        return $this->jsonAdminResult([
            'chartData' => $chartData,
            'property_chartData' => $property_chartData,
            'company_list' => $company_list
        ]);
    }
}
