define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'deal.deallist/index',
        add_url: 'deal.deallist/add',
        edit_url: 'deal.deallist/edit',
        delete_url: 'deal.deallist/delete',
        export_url: 'deal.deallist/export',
        modify_url: 'deal.deallist/modify',
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
                totalRow:true,
                parseData : function(res) { //res 即为原始返回的数据
                    $('#total').html('合计交易金额:'+res.totalRow.deal_money+' ;笔数:'+res.count);
                    return {
                        "code" : res.code, //解析接口状态
                        "msg" : res.msg, //解析提示文本
                        "count" : res.count, //解析数据长度
                        "data" : res.data //解析数据列表
                    };
                },
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    // {field: 'organization', title: '品牌'},
                    {field: 'merchant_name', title: '商户名称'},
                    {field: 'merchant_code', title: '商户号',sort:true},
                    {field: 'dls.name', title: '直属代理商名称',sort:true},
                    {field: 'sn', title: '终端sn'},
                    {field: 'top_code_deal', title: '一代编号'},
                    {field: 'deal_money', title: '交易金额',},
                    {field: 'service_money', title: '手续费'},
                    {
                        field: 'deal_type', title: '交易类型', search: 'select',
                        selectList: {0: '借记卡', 1: '信用卡'},
                    },
                    {field: 'deal_time', title: '交易完成时间',sort:true},
                    {field: 'deal_number', title: '渠道交易号'},
                    {field: 'deal_create_time', title: '创建时间',search: 'searchtime',sort:true},
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