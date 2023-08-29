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
    public function add(Request $request, Property $mProperty)
    {
        $params = $request->all();

        $number = $params['number'] ?? '';
        $company = $params['company'] ?? '';
        $property_type = $params['property_type'] ?? '';
        $property_name = $params['property_name'] ?? '';
        $address = $params['address'] ?? '';
        $area = $params['area'] ?? '';
        $term = $params['term'] ?? '';
        $rent = $params['rent'] ?? '';
        $notes = $params['notes'] ?? '';

        if (empty($number)){
            return $this->jsonAdminResult([],10001, '编号不能为空');
        }

        $info = $mProperty->where('number', $number)->first();
        $info = $this->dbResult($info);
        if (!empty($info)) {
            return $this->jsonAdminResult([],10001, '编号重复');
        }

        $time = date('Y-m-d H:i:s');
        $res = $mProperty->insert([
            'number' => $number,
            'company' => $company,
            'property_type' => $property_type,
            'property_name' => $property_name,
            'address' => $address,
            'area' => $area,
            'term' => $term,
            'rent' => $rent,
            'notes' => $notes,
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
    public function edit(Request $request, Property $mProperty)
    {
        $params = $request->all();

        $id = $params['id'] ?? 0;
        $number = $params['number'] ?? '';
        $company = $params['company'] ?? '';
        $property_type = $params['property_type'] ?? '';
        $property_name = $params['property_name'] ?? '';
        $address = $params['address'] ?? '';
        $area = $params['area'] ?? '';
        $term = $params['term'] ?? '';
        $rent = $params['rent'] ?? '';
        $notes = $params['notes'] ?? '';

        if (empty($id)) {
            return $this->jsonAdminResult([],10001, '参数错误');
        }

        if (empty($number)){
            return $this->jsonAdminResult([],10001, '编号不能为空');
        }

        $info = $mProperty->where('id', '!=', $id)->where('number', $number)->first();
        $info = $this->dbResult($info);
        if (!empty($info)) {
            return $this->jsonAdminResult([],10001, '编号重复');
        }

        $time = date('Y-m-d H:i:s');
        $res = $mProperty->where('id', $id)->update([
            'number' => $number,
            'company' => $company,
            'property_type' => $property_type,
            'property_name' => $property_name,
            'address' => $address,
            'area' => $area,
            'term' => $term,
            'rent' => $rent,
            'notes' => $notes,
            'updated_at' => $time
        ]);

        if ($res !== false) {
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
    public function del(Request $request, Property $mProperty)
    {
        $params = $request->all();

        $id = $params['id'] ?? 0;

        if (empty($id)) {
            return $this->jsonAdminResult([],10001,'参数错误');
        }

        $res = $mProperty->where('id', $id)->delete();

        if ($res !== false) {
            return $this->jsonAdminResultWithLog($request);
        } else {
            return $this->jsonAdminResult([],10001,'操作失败');
        }
    }
}
