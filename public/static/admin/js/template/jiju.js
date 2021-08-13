define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'template.jiju/index',
        add_url: 'template.jiju/add',
        edit_url: 'template.jiju/edit',
        delete_url: 'template.jiju/delete',
        export_url: 'template.jiju/export',
        modify_url: 'template.jiju/modify',
    };
    var soulTable = layui.soulTable;
    var Controller = {

        index: function () {
            ea.table.render({

                init: init, overflow: 'tips',
                skin: 'line  ' //行边框风格
                , even: true, //开启隔行背景
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'write.phone', title: '当前代理/创建人'},
                    {field: 'tname', title: '模板名称'},
                    {field: 'activity_condition', title: '激活条件'},
                    {field: 'activity_return', title: '激活返现'},
                    {field: 'activity_false', title: '伪激活'},
                    {field: 'activity_time', title: '激活截止时间'},
                    {field: 'reach_condition', title: '达标条件'},
                    {field: 'reach_return', title: '达标返现'},
                    {field: 'reach_time', title: '达标截止时间'},
                    // {field: 'status', title: '状态', templet: ea.table.switch},
                    {field: 'createtime', title: '创建时间'},
                    {field: 'updatetime', title: '更新时间'},
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