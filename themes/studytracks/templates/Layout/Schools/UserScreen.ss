<div class="row">
    <div class="col-md-5">
        <a href="/school/staff/$Staff.ID/edit" class="edit btn btn-default">edit user <i class="glyphicon glyphicon-pencil"></i></a>
        <a href="•" class="delete btn btn-danger" data-type="staff" data-id="$Staff.ID">delete user <i class="glyphicon glyphicon-remove"></i></a>
    </div>
</div>


<div class="row box-top">
    <div class="col-md-4 col-sm-6 box">
        <h4>Role</h4>
        <p>$Staff.Role</p>
    </div>
    <div class="col-md-4 col-sm-6 box">
        <h4>Email</h4>
        <p><a href="mailto:{$Staff.Email}">$Staff.Email</a></p>
    </div>
    <div class="col-md-4 col-sm-6 box">
        <h4>Added</h4>
        <p>$Staff.Created.Format('d/m/Y')</p>
    </div>
</div>