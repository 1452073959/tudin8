define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'terminal.freeze/index',
        add_url: 'terminal.freeze/add',
        edit_url: 'terminal.freeze/edit',
        delete_url: 'terminal.freeze/delete',
        export_url: 'terminal.freeze/export',
        modify_url: 'terminal.freeze/modify',
    };
    var soulTable = layui.soulTable;
    var Controller = {

        index: function () {
            ea.table.render({
                init: init, overflow: 'tips',
                skin: 'line  ' //行边框风格
                , even: true, //开启隔行背景
                toolbar: ['refresh',
                ],
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'sn', title: 'sn'},
                    {field: 'merchant_code', title: '商户号'},
                    {field: 'merchant_name', title: '商户名称'},
                    {field: 'type', title: '冻结项',search:'select', selectList:{"1": "服务费", "2": "流量费"}},
                    {field: 'status', title: '冻结结果',search:'select', selectList:{"1": "失败", "2": "成功"}},
                    {field: 'optNo', title: '冻结回复',search:'select', selectList:{"1": "失败", "2": "成功"}},
                    // {field: 'create_time', title: '时间'},
                    {field: 'time', title: '时间'},
                    {width: 250, title: '操作', templet: ea.table.tool},

                ]],
                done: function () {
                    // 在 done 中开启
                    soulTable.render(this)
                }

                , autoColumnWidth: {
                    init: true
                },
            });

            ea.listen();
        },
        add: function () {
            ea.listen();
        },
        edit: function () {
            ea.listen();
        },
    };
    return Controller;
});