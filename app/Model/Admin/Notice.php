<?php

namespace App\Model\Admin;

use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\FormatTrait;
use Illuminate\Support\Facades\DB;

class Notice extends Model
{
    use FormatTrait;
    public $table = 'notices';

    public function addRent($house_info) {
        $notice_list = [];
        $stat_list = [];
        if ($house_info['increase_type'] == 1) { // 递增
            $increase_content = json_decode($house_info['increase_content'], true);
            $year_price = $house_info['unit_price'] * $house_info['lease_area'] * 365;
            $begin_date = $house_info['stat_lease_date'];
            $begin_add_month = 0;
            foreach ($increase_content as $key => $value) {
                $temp_year_price = sprintf("%.2f", $year_price * (1 + 0.01 * $value['percent']));
                $year_price = $year_price * (1 + 0.01 * $value['percent']);
                $pay_method_count = 12 / $house_info['pay_method'];
                $stat_list[] = $temp_year_price;
                while ($pay_method_count--) {
                    $temp_begin_date = date('Y-m-d', strtotime("{$begin_add_month} months", strtotime($begin_date)));
                    $begin_add_month += $house_info['pay_method'];
                    $notice_list[] = [
                        'price' => sprintf("%.2f",$temp_year_price * $house_info['pay_method'] / 12),
                        'begin_date' => $temp_begin_date
                    ];
                }
            }
        } else { // 自定义
            $increase_content = json_decode($house_info['increase_content'], true);
            $begin_date = $house_info['stat_lease_date'];
            $begin_add_month = 0;
            foreach ($increase_content as $key => $value) {
                $temp_year_price = sprintf("%.2f", $value['unit_price'] * $house_info['lease_area'] * 365);
                $pay_method_count = 12 / $house_info['pay_method'];
                $stat_list[] = $temp_year_price;
                while ($pay_method_count--) {
                    $temp_begin_date = date('Y-m-d', strtotime("{$begin_add_month} months", strtotime($begin_date)));
                    $begin_add_month += $house_info['pay_method'];
                    $notice_list[] = [
                        'price' => sprintf("%.2f",$temp_year_price * $house_info['pay_method'] / 12),
                        'begin_date' => $temp_begin_date
                    ];
                }
            }
        }

        $property_notice_list = [];
        $property_stat_list = [];
        if ($house_info['property_increase_type'] == 1) { // 递增
            $property_increase_content = json_decode($house_info['property_increase_content'], true);
            $year_price = $house_info['property_unit_price'] * $house_info['lease_area'] * 12;
            $begin_date = $house_info['stat_lease_date'];
            $begin_add_month = 0;
            foreach ($property_increase_content as $key => $value) {
                $temp_year_price = sprintf("%.2f", $year_price * (1 + 0.01 * $value['percent']));
                $year_price = $year_price * (1 + 0.01 * $value['percent']);
                $pay_method_count = 12 / $house_info['property_pay_method'];
                $property_stat_list[] = $temp_year_price;
                while ($pay_method_count--) {
                    $temp_begin_date = date('Y-m-d', strtotime("{$begin_add_month} months", strtotime($begin_date)));
                    $begin_add_month += $house_info['pay_method'];
                    $property_notice_list[] = [
                        'price' => sprintf("%.2f",$temp_year_price * $house_info['property_pay_method'] / 12),
                        'begin_date' => $temp_begin_date
                    ];
                }
            }
        } else { // 自定义
            $property_increase_content = json_decode($house_info['property_increase_content'], true);
            $begin_date = $house_info['stat_lease_date'];
            $begin_add_month = 0;
            foreach ($property_increase_content as $key => $value) {
                $temp_year_price = sprintf("%.2f", $value['unit_price'] * $house_info['lease_area'] * 12);
                $pay_method_count = 12 / $house_info['property_pay_method'];
                $property_stat_list[] = $temp_year_price;
                while ($pay_method_count--) {
                    $temp_begin_date = date('Y-m-d', strtotime("{$begin_add_month} months", strtotime($begin_date)));
                    $begin_add_month += $house_info['pay_method'];
                    $property_notice_list[] = [
                        'price' => sprintf("%.2f",$temp_year_price * $house_info['property_pay_method'] / 12),
                        'begin_date' => $temp_begin_date
                    ];
                }
            }
        }

        // 公告信息
        $insert_notice_list = [];
        $mCompany = new Company();
        $company_user_id = $mCompany->where('id', $house_info['company_id'])->value('user_id');
        $user_id = $house_info['user_id'];
        $to_user_ids = array_merge([$company_user_id], [$user_id], [1]);
        $to_user_ids = array_unique($to_user_ids);
        $time = date('Y-m-d H:i:s');
        foreach ($to_user_ids as $user_id) {
            foreach ($notice_list as $value) {
                $insert_notice_list[] = [
                    'title' => $value['begin_date'] . '租金',
                    'source_table' => 'houses',
                    'source_id' => $house_info['id'],
                    'from' => 0,
                    'to' => $user_id,
                    'content' => '缴费时间：' . $value['begin_date'] . '；缴费金额：' . $value['price'],
                    'notice_date' => date('Y-m-d H:i:s', strtotime($value['begin_date']) - 15 * 24 * 60 * 60),
                    'type' => 2,
                    'created_at' => $time,
                    'updated_at' => $time
                ];
            }
        }
        foreach ($to_user_ids as $user_id) {
            foreach ($property_notice_list as $value) {
                $insert_notice_list[] = [
                    'title' => $value['begin_date'] . '物业',
                    'source_table' => 'houses',
                    'source_id' => $house_info['id'],
                    'from' => 0,
                    'to' => $user_id,
                    'content' => '缴费时间：' . $value['begin_date'] . '；缴费金额：' . $value['price'],
                    'notice_date' => date('Y-m-d H:i:s', strtotime($value['begin_date']) - 15 * 24 * 60 * 60),
                    'type' => 3,
                    'created_at' => $time,
                    'updated_at' => $time
                ];
            }
        }

        $this->where('source_table', 'houses')
            ->where('source_id', $house_info['id'])
            ->whereIn('type', [2,3])
            ->delete();
        $this->insert($insert_notice_list);

        // 年统计
        $begin_year = date('Y', strtotime($house_info['stat_lease_date']));
        $days = intval((time() - strtotime($house_info['stat_lease_date'])) / (24 * 60 * 60));
        if ($days > 365) {
            $days = 365;
        }
        $insert_stat_list = [];

        // 租金
        $before_price = 0;
        foreach ($stat_list as $value) {
            $after_price = sprintf("%.2f",$value / 365 * (365 - $days));
            $insert_stat_list[] = [
                'type' => 1, // 租金
                'company_id' => $house_info['company_id'],
                'house_id' => $house_info['id'],
                'year' => $begin_year,
                'price' => $before_price + $after_price,
                'created_at' => $time,
                'updated_at' => $time
            ];
            $before_price = $value - $after_price;
            $begin_year++;
        }
        if ($before_price > 0) {
            $insert_stat_list[] = [
                'type' => 1, // 租金
                'company_id' => $house_info['company_id'],
                'house_id' => $house_info['id'],
                'year' => $begin_year,
                'price' => $before_price,
                'created_at' => $time,
                'updated_at' => $time
            ];
        }

        // 物业费
        $before_price = 0;
        foreach ($property_stat_list as $value) {
            $after_price = sprintf("%.2f",$value / 365 * (365 - $days));
            $insert_stat_list[] = [
                'type' => 2, // 物业费
                'company_id' => $house_info['company_id'],
                'house_id' => $house_info['id'],
                'year' => $begin_year,
                'price' => $before_price + $after_price,
                'created_at' => $time,
                'updated_at' => $time
            ];
            $before_price = $value - $after_price;
            $begin_year++;
        }
        if ($before_price > 0) {
            $insert_stat_list[] = [
                'type' => 2, // 物业费
                'company_id' => $house_info['company_id'],
                'house_id' => $house_info['id'],
                'year' => $begin_year,
                'price' => $before_price,
                'created_at' => $time,
                'updated_at' => $time
            ];
        }

        $mStatPrice = new StatPrice();
        $mStatPrice->where('company_id', $house_info['company_id'])
            ->where('house_id', $house_info['id'])
            ->whereIn('type', [1,2])
            ->delete();
        $mStatPrice->insert($insert_stat_list);
    }
}
