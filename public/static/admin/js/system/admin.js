define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'system.admin/index',
        add_url: 'system.admin/add',
        edit_url: 'system.admin/edit',
        delete_url: 'system.admin/delete',
        modify_url: 'system.admin/modify',
        export_url: 'system.admin/export',
        password_url: 'system.admin/password',
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
                    {type: "checkbox"},
                    {field: 'id', width: 80, title: 'ID'},
                    {field: 'sort', width: 80, title: '排序', edit: 'text'},
                    {field: 'username', minWidth: 80, title: '登录账户'},
                    {field: 'head_img', minWidth: 80, title: '头像', search: false, templet: ea.table.image},
                    // {field: 'name', minWidth: 80, title: '真实姓名'},
                    {field: 'company', minWidth: 80, title: '公司名称'},
                    {field: 'appid', minWidth: 80, title: '代理商编号'},
                    {field: 'secret_key', minWidth: 80, title: '秘钥'},
                    {field: 'appname', minWidth: 80, title: '代理商app名称'},
                    {field: 'phone', minWidth: 80, title: '手机'},
                    {field: 'card_number', minWidth: 80, title: '身份证号码'},
                    {field: 'bank', minWidth: 80, title: '银行'},
                    {field: 'name', minWidth: 80, title: '姓名'},
                    {field: 'bank_number', minWidth: 80, title: '银行卡号'},
                    {field: 'pushing_code', minWidth: 80, title: '推广号码'},
                    {field: 'higher_level_id', minWidth: 80, title: '所属代理商编号'},
                    // {field: 'login_num', minWidth: 80, title: '登录次数'},
                    {field: 'remark', minWidth: 80, title: '备注信息'},
                    {
                        field: 'status',
                        title: '状态',
                        width: 85,
                        search: 'select',
                        selectList: {0: '禁用', 1: '启用'},
                        templet: ea.table.switch
                    },
                    {field: 'create_time', minWidth: 80, title: '创建时间', search: 'range'},
                    {
                        width: 250,
                        title: '操作',
                        templet: ea.table.tool,
                        operat: [
                            'edit',
                            [{
                                text: '设置密码',
                                url: init.password_url,
                                method: 'open',
                                auth: 'password',
                                class: 'layui-btn layui-btn-normal layui-btn-xs',
                            }],
                            'delete'
                        ]
                    }
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

        agent: function () {
            var init = {
                table_elem: '#currentTable',
                table_render_id: 'currentTableRenderId',
                index_url: 'system.admin/agent',
                add_url: 'system.admin/add',
                history_url: 'deal.profit/agent_profit',
                delete_url: 'system.admin/delete',
                modify_url: 'system.admin/modify',
                export_url: 'system.admin/export',
                password_url: 'system.admin/password',
            };
            ea.table.render({
                init: init, overflow: 'tips',
                skin: 'line  ' //行边框风格
                , even: true, //开启隔行背景
                cols: [[
                    {type: "checkbox"},
                    {field: 'id', width: 80, title: 'ID'},
                    {field: 'name', minWidth: 80, title: '代理商名称'},
                    {field: 'profit_balance', minWidth: 80, title: '分润记账金额'},
                    {field: 'return_balance', minWidth: 80, title: '返现记账金额'},
                    {
                        width: 250,
                        title: '操作',
                        templet: ea.table.tool,
                        operat: [
                            [{
                                text: '历史记录',
                                url: init.history_url,
                                method: 'open',
                                auth: 'history',
                                class: 'layui-btn layui-btn-xs layui-btn-success',
                                extend: 'data-full="true"',
                            },],
                        ]
                    }
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
        password: function () {
            ea.listen();
        }
    };
    return Controller;
});