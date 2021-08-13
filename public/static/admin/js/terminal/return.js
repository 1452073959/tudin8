define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'terminal.return/index',
        add_url: 'terminal.return/add',
        edit_url: 'terminal.return/edit',
        delete_url: 'terminal.return/delete',
        export_url: 'terminal.return/export',
        modify_url: 'terminal.return/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'form_id', title: '回拨人'},
                    {field: 'to_id', title: '回拨上级'},
                    {field: 'terminnal_id', title: '机具id'},
                    {field: 'status', title: '1失败2成功', templet: ea.table.switch},
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