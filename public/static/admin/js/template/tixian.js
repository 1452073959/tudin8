define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'template.tixian/index',
        add_url: 'template.tixian/add',
        edit_url: 'template.tixian/edit',
        delete_url: 'template.tixian/delete',
        export_url: 'template.tixian/export',
        modify_url: 'template.tixian/modify',
    };
    var soulTable = layui.soulTable;
    var Controller = {

        index: function () {
            ea.table.render({
                init: init, overflow: 'tips',
                skin: 'line  ' //行边框风格
                , even: true, //开启隔行背景
                // size: 'sm', //小尺寸的表格
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'write.phone', title: '当前代理//创建人'},
                    {field: 'tname', title: '模板名称'},
                    {field: 'fenrun_rate', title: '分润余额税率'},
                    {field: 'fenrun_sxf', title: '分润余额单笔手续费'},
                    {field: 'jijufan_rate', title: '机具返现余额税率'},
                    {field: 'jijufan_sxf', title: '机具返现余额单笔手续费'},
                    {field: 'fwf_rate', title: 'D0服务费税率'},
                    {field: 'fwf_sxf', title: 'D0服务费单笔手续费'},
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