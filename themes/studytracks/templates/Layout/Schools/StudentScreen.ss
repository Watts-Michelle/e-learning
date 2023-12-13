<% if $CanAdd %>
<div class="row">
    <div class="col-md-5">
        <a href="/school/students/$Student.UUID/edit" class="edit btn btn-default">edit user <i class="glyphicon glyphicon-pencil"></i></a>
        <a href="#" class="delete btn btn-danger" data-type="student" data-id="$Student.UUID">delete user <i class="glyphicon glyphicon-remove"></i></a>
    </div>
</div>
<% end_if %>
<div class="row box-top">
    <div class="col-md-4 col-sm-6 box">
        <h4>Class</h4>
        <p>

            <% if $Student.SchoolClasses %>
                <% loop $Student.SchoolClasses %>
                    <% with $CurrentMember %>
                        <% if $ID == Up.StaffID || $Role != 'Teacher' %>
                            <a href="/school/classes/$Up.ID">$Up.Name</a><% if not $Up.Last %>,<% end_if %>
                        <% else %>
                            $Up.Name<% if not $Up.Last %>,<% end_if %>
                        <% end_if %>
                    <% end_with %>
                <% end_loop %>
            <% else %>
                not in classes
            <% end_if %>
        </p>
    </div>
    <div class="col-md-4 col-sm-6 box">
        <h4>Email</h4>
        <p><a href="mailto:{$Student.Email}">$Student.Email</a></p>
    </div>
    <div class="col-md-4 col-sm-6 box">
        <h4>Added</h4>
        <p>$Student.Created.Format('d/m/Y')</p>
    </div>
</div>