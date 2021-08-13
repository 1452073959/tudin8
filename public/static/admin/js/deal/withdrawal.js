define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'deal.withdrawal/index',
        add_url: 'deal.withdrawal/add',
        edit_url: 'deal.withdrawal/edit',
        delete_url: 'deal.withdrawal/delete',
        export_url: 'deal.withdrawal/export',
        modify_url: 'deal.withdrawal/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'name', title: '姓名'},
                    {field: 'account', title: '账号'},
                    {field: 'type', title: '账号',search: 'select', selectList: {2: '返现提现', 1: '分润提现'},},
                    {field: 'status', title: '打款状态', width: 85, search: 'select', selectList: {1: '未打款', 1: '已打款'}, templet: ea.table.switch},
                    {field: 'money', title: '提现金额'},
                    {field: 'create_time', title: '创建时间'},
                    {width: 250, title: '操作', templet: ea.table.tool},

                ]],
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