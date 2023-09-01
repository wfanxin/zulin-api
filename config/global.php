<?php

return [
    'page_size' => 20,

    'pay_method_list' => [
        ['label' => '月', 'value' => 1],
        ['label' => '季度', 'value' => 3],
        ['label' => '半年', 'value' => 6],
        ['label' => '年', 'value' => 12],
    ],
    'increase_type_list' => [
        ['label' => '递增', 'value' => 1],
        ['label' => '自定义', 'value' => 2],
    ],
    'status_options' => [
        ['label' => '待提交', 'value' => 0],
        ['label' => '审批中', 'value' => 1],
        ['label' => '审批通过', 'value' => 2],
        ['label' => '审批失败', 'value' => 3],
    ],
    'approval_status_options' => [
        ['label' => '待审批', 'value' => 1],
        ['label' => '审批通过', 'value' => 2],
    ],
    'read_status_options' => [
        ['label' => '未读', 'value' => 0],
        ['label' => '已读', 'value' => 1],
    ]
];
