define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'template.jiesuan/index',
        add_url: 'template.jiesuan/add',
        edit_url: 'template.jiesuan/edit',
        delete_url: 'template.jiesuan/delete',
        export_url: 'template.jiesuan/export',
        modify_url: 'template.jiesuan/modify',
    };
    var soulTable = layui.soulTable;
    var Controller = {

        index: function () {
            ea.table.render({
                init: init, overflow: 'tips',
                skin: 'line  ' //行边框风格
                , even: true, //开启隔行背景
                size: 'sm', //小尺寸的表格
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'write.phone', title: '当前代理/创建人'},
                    {field: 'tname', title: '模板名称'},
                    {field: 'cFeeRate', title: '信用卡手续费费率'},
                    {field: 'dFeeRate', title: '借记卡手续费费率'},
                    {field: 'dFeeMax', title: '借记卡手续费最大值(元)'},
                    {field: 'wechatPayFeeRate', title: '微信手续费费率'},
                    {field: 'alipayFeeRate', title: '支付宝手续费费率'},
                    {field: 'ycFreeFeeRate', title: '云闪付信用卡手续费费率'},
                    {field: 'ydFreeFeeRate', title: '云闪付借记卡手续费费率'},
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