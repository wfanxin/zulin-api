<?php

namespace App\Http\Controllers\Admin\Lease;

use App\Http\Controllers\Admin\Controller;
use App\Http\Traits\FormatTrait;
use App\Model\Admin\Company;
use App\Model\Admin\House;
use App\Model\Admin\Property;
use App\Model\Admin\User;
use Illuminate\Http\Request;

/**
 * @name 租赁合同
 * Class HouseController
 * @package App\Http\Controllers\Admin\Lease
 *
 * @Resource("houses")
 */
class HouseController extends Controller
{
    use FormatTrait;

    /**
     * @name 租赁合同列表
     * @Get("/lv/lease/house/list")
     * @Version("v1")
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     **/
    public function list(Request $request, House $mHouse, User $mUser, Company $mCompany)
    {
        $params = $request->all();
        $params['userId'] = $request->userId;

        $where = [];

        $userInfo = $mUser->getCurUser($params['userId']);
        if (!in_array('admin', $userInfo['roles'])) { // 不是超级管理员，查看自己创建的合同
            $where[] = ['user_id', '=', $params['userId']];
        }

        // 租赁合同
        if (!empty($params['company_name'])){
            $company_ids = $mCompany->where('company_name', 'like', '%' . $params['company_name'] . '%')->get(['id']);
            $company_ids = $this->dbResult($company_ids);
            $company_ids = array_column($company_ids, 'id');
            $where[] = [function ($query) use ($company_ids) {
                $query->whereIn('company_id', $company_ids);
            }];
        }

        // 商铺号
        if (!empty($params['shop_number'])){
            $where[] = ['shop_number', 'like', '%' . $params['shop_number'] . '%'];
        }

        $orderField = 'id';
        $sort = 'desc';
        $page = $params['page'] ?? 1;
        $pageSize = $params['pageSize'] ?? config('global.page_size');
        $data = $mHouse->where($where)
            ->orderBy($orderField, $sort)
            ->paginate($pageSize, ['*'], 'page', $page);

        $company_list = $mCompany->get(['id', 'company_name']);
        $company_list = $this->dbResult($company_list);

        // 租赁公司
        if (!empty($data->items())) {
            $company_arr = array_column($company_list, null, 'id');
            foreach ($data->items() as $k => $v){
                $data->items()[$k]['company_name'] = $company_arr[$v->company_id]['company_name'] ?? '';
            }
        }

        return $this->jsonAdminResult([
            'total' => $data->total(),
            'data' => $data->items(),
            'company_list' => $company_list,
            'pay_method_list' => config('global.pay_method_list'),
            'increase_type_list' => config('global.increase_type_list')
        ]);
    }

    /**
     * @name 添加租赁合同
     * @Post("/lv/lease/house/add")
     * @Version("v1")
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     **/
    public function add(Request $request, House $mHouse)
    {
        $params = $request->all();
        $params['userId'] = $request->userId;

        // 租金
        $company_id = $params['company_id'] ?? '';
        $shop_number = $params['shop_number'] ?? '';
        $lease_area = $params['lease_area'] ?? '';
        $begin_lease_date = $params['begin_lease_date'] ?? '';
        $stat_lease_date = $params['stat_lease_date'] ?? '';
        $lease_year = $params['lease_year'] ?? '';
        $repair_period = $params['repair_period'] ?? '';
        $category = $params['category'] ?? '';
        $contract_number = $params['contract_number'] ?? '';
        $unit_price = $params['unit_price'] ?? '';
        $performance_bond = $params['performance_bond'] ?? '';
        $pay_method = $params['pay_method'] ?? '';
        $increase_type = $params['increase_type'] ?? '';

        // 物业费
        $property_contract_number = $params['property_contract_number'] ?? '';
        $property_safety_person = $params['property_safety_person'] ?? '';
        $property_contact_info = $params['property_contact_info'] ?? '';
        $property_unit_price = $params['property_unit_price'] ?? '';
        $property_pay_method = $params['property_pay_method'] ?? '';
        $property_increase_type = $params['property_increase_type'] ?? '';

        if (empty($company_id)){
            return $this->jsonAdminResult([],10001, '请选择租赁公司');
        }

        if (empty($shop_number)){
            return $this->jsonAdminResult([],10001, '商铺号不能为空');
        }

        if (empty($lease_area)){
            return $this->jsonAdminResult([],10001, '租赁面积不能为空');
        }

        if (empty($lease_year)){
            return $this->jsonAdminResult([],10001, '租赁年限不能为空');
        }

        if (empty($unit_price)){
            return $this->jsonAdminResult([],10001, '租金单价不能为空');
        }

        if (empty($pay_method)){
            return $this->jsonAdminResult([],10001, '请选择租金支付方式');
        }

        if (empty($property_unit_price)){
            return $this->jsonAdminResult([],10001, '物业单价不能为空');
        }

        if (empty($property_pay_method)){
            return $this->jsonAdminResult([],10001, '请选择物业支付方式');
        }

        $time = date('Y-m-d H:i:s');
        $res = $mHouse->insert([
            'user_id' => $params['userId'],
            'company_id' => $company_id,
            'shop_number' => $shop_number,
            'lease_area' => $lease_area,
            'begin_lease_date' => $begin_lease_date,
            'stat_lease_date' => $stat_lease_date,
            'lease_year' => $lease_year,
            'repair_period' => $repair_period,
            'category' => $category,
            'contract_number' => $contract_number,
            'unit_price' => $unit_price,
            'performance_bond' => $performance_bond,
            'pay_method' => $pay_method,
            'increase_type' => $increase_type,
            'property_contract_number' => $property_contract_number,
            'property_safety_person' => $property_safety_person,
            'property_contact_info' => $property_contact_info,
            'property_unit_price' => $property_unit_price,
            'property_pay_method' => $property_pay_method,
            'property_increase_type' => $property_increase_type,
            'created_at' => $time,
            'updated_at' => $time
        ]);

        if ($res) {
            return $this->jsonAdminResultWithLog($request);
        } else {
            return $this->jsonAdminResult([],10001,'操作失败');
        }
    }

    /**
     * @name 修改租赁合同
     * @Post("/lv/lease/house/edit")
     * @Version("v1")
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     **/
    public function edit(Request $request, House $mHouse)
    {
        $params = $request->all();

        $id = $params['id'] ?? 0;

        if (empty($id)) {
            return $this->jsonAdminResult([],10001, '参数错误');
        }

        $info = $mHouse->where('id', $id)->first();
        $info = $this->dbResult($info);
        if (empty($info)) {
            return $this->jsonAdminResult([],10001, '参数错误');
        }

        // 租金
        $company_id = $params['company_id'] ?? '';
        $shop_number = $params['shop_number'] ?? '';
        $lease_area = $params['lease_area'] ?? '';
        $begin_lease_date = $params['begin_lease_date'] ?? '';
        $stat_lease_date = $params['stat_lease_date'] ?? '';
        $lease_year = $params['lease_year'] ?? '';
        $repair_period = $params['repair_period'] ?? '';
        $category = $params['category'] ?? '';
        $contract_number = $params['contract_number'] ?? '';
        $unit_price = $params['unit_price'] ?? '';
        $performance_bond = $params['performance_bond'] ?? '';
        $pay_method = $params['pay_method'] ?? '';
        $increase_type = $params['increase_type'] ?? '';

        // 物业费
        $property_contract_number = $params['property_contract_number'] ?? '';
        $property_safety_person = $params['property_safety_person'] ?? '';
        $property_contact_info = $params['property_contact_info'] ?? '';
        $property_unit_price = $params['property_unit_price'] ?? '';
        $property_pay_method = $params['property_pay_method'] ?? '';
        $property_increase_type = $params['property_increase_type'] ?? '';

        if (empty($company_id)){
            return $this->jsonAdminResult([],10001, '请选择租赁公司');
        }

        if (empty($shop_number)){
            return $this->jsonAdminResult([],10001, '商铺号不能为空');
        }

        if (empty($lease_area)){
            return $this->jsonAdminResult([],10001, '租赁面积不能为空');
        }

        if (empty($lease_year)){
            return $this->jsonAdminResult([],10001, '租赁年限不能为空');
        }

        if (empty($unit_price)){
            return $this->jsonAdminResult([],10001, '租金单价不能为空');
        }

        if (empty($pay_method)){
            return $this->jsonAdminResult([],10001, '请选择租金支付方式');
        }

        if (empty($property_unit_price)){
            return $this->jsonAdminResult([],10001, '物业单价不能为空');
        }

        if (empty($property_pay_method)){
            return $this->jsonAdminResult([],10001, '请选择物业支付方式');
        }

        $time = date('Y-m-d H:i:s');
        $res = $mHouse->where('id', $id)->update([
            'company_id' => $company_id,
            'shop_number' => $shop_number,
            'lease_area' => $lease_area,
            'begin_lease_date' => $begin_lease_date,
            'stat_lease_date' => $stat_lease_date,
            'lease_year' => $lease_year,
            'repair_period' => $repair_period,
            'category' => $category,
            'contract_number' => $contract_number,
            'unit_price' => $unit_price,
            'performance_bond' => $performance_bond,
            'pay_method' => $pay_method,
            'increase_type' => $increase_type,
            'property_contract_number' => $property_contract_number,
            'property_safety_person' => $property_safety_person,
            'property_contact_info' => $property_contact_info,
            'property_unit_price' => $property_unit_price,
            'property_pay_method' => $property_pay_method,
            'property_increase_type' => $property_increase_type,
            'updated_at' => $time
        ]);

        if ($res) {
            return $this->jsonAdminResultWithLog($request);
        } else {
            return $this->jsonAdminResult([],10001,'操作失败');
        }
    }

    /**
     * @name 删除租赁合同
     * @Post("/lv/lease/house/del")
     * @Version("v1")
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     **/
    public function del(Request $request, House $mHouse)
    {
        $params = $request->all();

        $id = $params['id'] ?? 0;

        if (empty($id)) {
            return $this->jsonAdminResult([],10001,'参数错误');
        }

        $res = $mHouse->where('id', $id)->delete();

        if ($res !== false) {
            return $this->jsonAdminResultWithLog($request);
        } else {
            return $this->jsonAdminResult([],10001,'操作失败');
        }
    }
}
