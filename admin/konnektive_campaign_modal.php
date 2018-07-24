<div class="modal fade" id="category_add_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Campaign Category Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning category_add_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:10px;">
                        <div class="col-xs-4 modal_input_label" style="text-align: left;">Category Name</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm add_category_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-12 modal_input_label" style="text-align: left;">Campaign List</div>
                    </div>
                    <div style="max-height:600px;overflow-y:auto;">
                        <table class="table table-hover" style="border-left: 1px solid #dadada; border-right: 1px solid #dadada">
                            <thead>
                                <tr>
                                    <th><input id="all_add_campaign" type="checkbox" style="margin:0;padding:0"></input></th>
                                    <th>Campaign ID</th>
                                    <th>Campaign Name</th>
                                </tr>
                            </thead>
                            <tbody class="table_add_category_body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_category_add">Add Category</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="category_edit_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Campaign Category Dialog</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="alert alert-warning category_edit_alert" role="alert" style="display:none"></div>
                    <div class="row" style="margin-bottom:10px;">
                        <div class="col-xs-4 modal_input_label" style="text-align: left;">Category Name</div>
                        <div class="col-xs-8"><input type="text" class="form-control input-sm edit_category_name"></div>
                    </div>
                    <div class="row" style="margin-bottom:5px;">
                        <div class="col-xs-12 modal_input_label" style="text-align: left;">Campaign List</div>
                    </div>
                    <div style="max-height:600px;overflow-y:auto;">
                        <table class="table table-hover" style="border-left: 1px solid #dadada; border-right: 1px solid #dadada">
                            <thead>
                                <tr>
                                    <th><input id="all_edit_campaign" type="checkbox" style="margin:0;padding:0"></input></th>
                                    <th>Campaign ID</th>
                                    <th>Campaign Name</th>
                                </tr>
                            </thead>
                            <tbody class="table_edit_category_body">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_category_edit">Edit Category</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="category_delete_modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Message</h4>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    Do you want to delete this category?
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success modal_btn_category_delete">Delete Category</button>
            </div>
        </div>
    </div>
</div>
