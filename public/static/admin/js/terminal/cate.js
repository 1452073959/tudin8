define(["jquery", "easy-admin"], function ($, ea) {

    var init = {
        table_elem: '#currentTable',
        table_render_id: 'currentTableRenderId',
        index_url: 'terminal.cate/index',
        add_url: 'terminal.cate/add',
        edit_url: 'terminal.cate/edit',
        delete_url: 'terminal.cate/delete',
        export_url: 'terminal.cate/export',
        modify_url: 'terminal.cate/modify',
    };

    var Controller = {

        index: function () {
            ea.table.render({
                init: init,
                cols: [[
                    {type: 'checkbox'},
                    {field: 'id', title: 'id'},
                    {field: 'brand', title: '品牌'},
                    {field: 'factory', title: '厂家'},
                    {field: 'model', title: '型号'},
                    {field: 'appid', title: '机构号'},
                    {field: 'secret_key', title: '秘钥'},
                    {field: 'img', title: '图片', templet: ea.table.image},
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