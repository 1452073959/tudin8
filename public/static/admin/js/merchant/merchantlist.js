define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'merchant.merchantlist/index',
        add_url: 'merchant.merchantlist/add',
        edit_url: 'merchant.merchantlist/edit',
        delete_url: 'merchant.merchantlist/delete',
        export_url: 'merchant.merchantlist/export',
        modify_url: 'merchant.merchantlist/modify',
    };
    var soulTable = layui.soulTable;
    var Controller = {

        index: function () {
            ea.table.render({
                init: init, overflow: 'tips',
                skin: 'line  ' //行边框风格
                , even: true, //开启隔行背景
                size: 'sm', //小尺寸的表格
                toolbar: ['refresh',
                ],
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'merchant_title', title: '商户名称'},
                    {field: 'corporate_name', title: '法人名称'},
                    {field: 'merchant_code', title: '商户编号'},
                    {field: 'tel', title: '手机号码'},
                    {field: 'deal_sum', title: '累计交易'},
                    {field: 'dls.name', title: '直属代理商电话'},
                    // {field: 'dls.appid', title: '直属代理商编号'},
                    {field: 'cate.brand', title: '机具型号'},
                    {field: 'sn', title: '机具sn'},
                    {field: 'top_code', title: '一代机构号'},
                    {field: 'cFeeRate', title: '信用卡手续费费率(%)'},
                    {field: 'dFeeRate', title: '借记卡手续费费率(%)'},
                    {field: 'dFeeMax', title: '借记卡手续费最大值(元)'},
                    {field: 'wechatPayFeeRate', title: '微信手续费费率(%)'},
                    {field: 'alipayFeeRate', title: '支付宝手续费费率(%)'},
                    {field: 'ycFreeFeeRate', title: '云闪付信用卡手续费费率(%)'},
                    {field: 'ydFreeFeeRate', title: '云闪付借记卡手续费费率(%)(%)'},
                    {field: 'create_time', title: '创建时间'},
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