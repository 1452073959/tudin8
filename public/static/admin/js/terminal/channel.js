define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'terminal.channel/index',
        add_url: 'terminal.channel/add',
        edit_url: 'terminal.channel/edit',
        delete_url: 'terminal.channel/delete',
        export_url: 'terminal.channel/export',
        modify_url: 'terminal.channel/modify',
        grant_url: 'terminal.channel/grant',
        batch_url: 'terminal.channel/batch',
        activity_url: 'terminal.channel/activity',
        activity_sim_url: 'terminal.channel/activity_sim',
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
                    [{
                        text: '终端下拨',
                        url: init.grant_url,
                        method: 'open',
                        auth: 'grant',
                        checkbox: true,
                        class: 'layui-btn layui-btn-normal layui-btn-sm',
                    }],
                    [{
                        text: '批量入库',
                        url: init.batch_url,
                        method: 'open',
                        auth: 'batch',
                        class: 'layui-btn layui-btn-normal layui-btn-sm',
                    }],
                    'delete', 'add'],
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id',search:false},
                    {field: 'cate.type', title: '品牌'},
                    {field: 'cate.brand', title: '终端型号'},
                    {field: 'sn', title: '终端sn'},
                    // {field: 'dls.username', title: '代理商id'},
                    {
                        title: '代理商', templet: function (d) {
                            if (d.dls == null) {
                                return ''
                            } else {
                                return d.dls.name
                            }
                        }
                    },
                    {field: 'top_code', title: '一代编号'},
                    {field: 'merchant_code', title: '商户编号'},
                    {field: 'merchant_title', title: '商户名称'},

                    // {field: 'terminal_outlet', title: '终端厂家'},
                    {
                        field: 'status',
                        search: 'select',
                        selectList: {'0':"入库", 1:"未激活",2: "已激活未达标",3: "已激活未达标", 4:"已达标",5: "超期未达标",6: "超期未激活",7:'未参与活动已绑定商户'},
                        title: '终端状态 ',
                    },
                    // {
                    //     title: '结算模板', templet: function (d) {
                    //         if (d.jiesuan == null) {
                    //             return ''
                    //         } else {
                    //             return d.jiesuan.tname
                    //         }
                    //     }
                    // },
                    // {
                    //     title: '机具模板', templet: function (d) {
                    //         if (d.jiju == null) {
                    //             return ''
                    //         } else {
                    //             return d.jiju.tname
                    //         }
                    //     }
                    // },
                    // {
                    //     title: '提现模板', templet: function (d) {
                    //         if (d.tixian == null) {
                    //             return ''
                    //         } else {
                    //             return d.tixian.tname
                    //         }
                    //     }
                    // },

                    {
                        field: 'activity', title: '参与服务费活动', search: 'select',
                        selectList: {"1": "不参与", "2": "参与"},
                    },
                    {
                        field: 'activity_sim', title: '参与sim活动', search: 'select',
                        selectList: {"1": "不参与", "2": "参与"},
                    },
                    {field: 'di_service_charge', title: '冻结服务费',search:false},
                    {field: 'sim_service_charge', title: 'sim服务费',search:false},
                    {field: 'sim_day', title: 'sim扣费(天)',search:false},
                    {field: 'pos_note_template', title: 'pos短信扣费模板',search:false},
                    {field: 'sim_note_template', title: 'sim短信扣费模板',search:false},
                    {field: 'optNo', title: '服务费冻结回复',search:false},
                    {field: 'optNo_sim', title: '流量费冻结回复',search:false},
                    {field: 'activity_freeze_status_sim', title: 'sim扣款状态',search:'select', selectList:{"1": "未扣款", "2": "已扣款"}},
                    {field: 'activity_freeze_status', title: '服务费扣款状态',search:'select', selectList:{"1": "未扣款", "2": "已扣款"}},
                    {field: 'return_activate', title: '已激活返现',search:'select', selectList:{"1": "已返现", "0": "未返现"}},
                    {field: 'retuen_reach', title: '已达标返现',search:'select', selectList:{"1": "已返现", "0": "未返现"}},
                    {field: 'create_time', title: '创建时间',search:false},
                    {
                        width: 250, title: '操作', templet: ea.table.tool, fixed: "right", operat: [
                            [
                                {
                                    text: '终端下拨',
                                    url: init.grant_url,
                                    method: 'open',
                                    auth: 'grant',
                                    class: 'layui-btn layui-btn-xs layui-btn-normal',
                                },
                                {
                                    text: '发起服务费冻结',
                                    url: init.activity_url,
                                    method: 'request',
                                    auth: 'activity',
                                    class: 'layui-btn layui-btn-xs layui-btn-normal',
                                },
                                {
                                    text: '发起sim冻结',
                                    url: init.activity_sim_url,
                                    method: 'request',
                                    auth: 'activity_sim',
                                    class: 'layui-btn layui-btn-xs layui-btn-normal',
                                }
                                ],
                            'edit','delete']
                    },

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