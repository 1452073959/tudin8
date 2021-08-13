define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'deal.profit/index',
        add_url: 'deal.profit/add',
        edit_url: 'deal.profit/edit',
        delete_url: 'deal.profit/delete',
        export_url: 'deal.profit/export',
        modify_url: 'deal.profit/modify',
    };
    var soulTable = layui.soulTable;
    var Controller = {
        index: function () {

            ea.table.render({
                init: init, overflow: 'tips',
                skin: 'line  ' //行边框风格
                , even: true, //开启隔行背景
                // size: 'sm', //小尺寸的表格
                toolbar: ['refresh',
                ],
                parseData : function(res) { //res 即为原始返回的数据
                    $('#total').html('合计交易金额:'+res.totalRow.deal_money+' ;笔数:'+res.count+' ;分润:'+res.totalRow.profit);
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
                    {field: 'dls.name', title: '当前代理ID'},
                    {field: 'terminal.merchant_title', title: '收款商户'},
                    {field: 'terminal.sn', title: '收款机具'},
                    // {field: 'terminal.brand', title: '机具品牌'},
                    {field: 'amount', title: '交易额'},
                    {field: 'profit', title: '利润'},
                    {field: 'tranTime', title: '交易时间',search: 'searchtime'},
                    {field: 'tranCode', title: '交易码'},
                    {field: 'field', title: '当前费率字段'},
                    {field: 'createtime', title: '创建时间',search: 'searchtime'},
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
        dl: function () {
            var init = {
                table_elem: '#currentTable',
                table_render_id: 'currentTableRenderId',
                index_url: 'deal.profit/dl',
                add_url: 'deal.profit/add',
                edit_url: 'deal.profit/edit',
                delete_url: 'deal.profit/delete',
                export_url: 'deal.profit/export',
                modify_url: 'deal.profit/modify',
            };
            ea.table.render({
                init: init, overflow: 'tips',
                skin: 'line  ' //行边框风格
                , even: true, //开启隔行背景
                // size: 'sm', //小尺寸的表格
                toolbar: ['refresh',
                ],
                parseData : function(res) { //res 即为原始返回的数据
                    $('#total').html('合计交易金额:'+res.totalRow.deal_money+' ;笔数:'+res.count+' ;分润:'+res.totalRow.profit);
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
                    {field: 'dls.name', title: '当前代理ID'},
                    {field: 'terminal.merchant_title', title: '收款商户'},
                    {field: 'terminal.sn', title: '收款机具'},
                    // {field: 'terminal.brand', title: '机具品牌'},
                    {field: 'amount', title: '交易额'},
                    {field: 'profit', title: '利润'},
                    {field: 'tranTime', title: '交易时间',search: 'searchtime'},
                    {field: 'tranCode', title: '交易码'},
                    {field: 'field', title: '当前费率字段'},
                    {field: 'createtime', title: '创建时间',search: 'searchtime'},
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
        dlfx: function () {
            var init = {
                table_elem: '#currentTable',
                table_render_id: 'currentTableRenderId',
                index_url: 'deal.profit/dlfx',
                add_url: 'deal.profit/add',
                edit_url: 'deal.profit/edit',
                delete_url: 'deal.profit/delete',
                export_url: 'deal.profit/export',
                modify_url: 'deal.profit/modify',
            };
            ea.table.render({
                init: init, overflow: 'tips',
                skin: 'line  ' //行边框风格
                , even: true, //开启隔行背景
                // size: 'sm', //小尺寸的表格
                toolbar: ['refresh',
                ],
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'dls.name', title: '当前代理ID'},
                    {field: 'terminal.merchant_title', title: '收款商户'},
                    // {field: 'terminal.sn', title: '收款机具'},
                    // {field: 'terminal.brand', title: '机具品牌'},
                    // {field: 'amount', title: '交易额'},
                    {field: 'profit', title: '返现金额'},
                    {field: 'tranTime', title: '交易时间'},
                    // {field: 'tranCode', title: '交易码'},
                    // {field: 'field', title: '当前费率字段'},
                    {field: 'createtime', title: '创建时间',search: 'searchtime'},
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

        agent_profit: function () {
            var init = {
                table_elem: '#currentTable',
                table_render_id: 'currentTableRenderId',
                index_url: 'deal.profit/agent_profit',
                add_url: 'deal.profit/add',
                edit_url: 'deal.profit/edit',
                delete_url: 'deal.profit/delete',
                export_url: 'deal.profit/export',
                modify_url: 'deal.profit/modify',
            };
            ea.table.render({
                init: init, overflow: 'tips',
                skin: 'line  ' //行边框风格
                , even: true, //开启隔行背景
                // size: 'sm', //小尺寸的表格
                toolbar: ['refresh',
                ],
                parseData : function(res) { //res 即为原始返回的数据
                    $('#total').html('合计交易金额:'+res.totalRow.deal_money+' ;笔数:'+res.count+' ;分润:'+res.totalRow.profit);
                    return {
                        "code" : res.code, //解析接口状态
                        "msg" : res.msg, //解析提示文本
                        "count" : res.count, //解析数据长度
                        "data" : res.data //解析数据列表
                    };
                },
                where: {id: id},//如果无需传递额外参数，可不加该参数
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'dls.name', title: '当前代理ID'},
                    {field: 'terminal.merchant_title', title: '收款商户'},
                    {field: 'type', title: '入账类型',selectList: {"1": "分润", "2": "返现", "0": "未确定"},},
                    // {field: 'terminal.brand', title: '机具品牌'},
                    {field: 'amount', title: '交易额'},
                    {field: 'profit', title: '返现金额'},
                    {field: 'tranTime', title: '交易时间'},
                    // {field: 'tranCode', title: '交易码'},
                    // {field: 'field', title: '当前费率字段'},
                    {field: 'createtime', title: '创建时间',search: 'searchtime'},
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