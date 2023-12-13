<% if $CanAdd %>
    <a href="/school/classes/create" class="btn btn-add">Add class</a>
<% end_if %>
$ExtraButtons
<div class="table-responsive">
    <table class="table">
        <thead>
        <tr>
            <th>Class</th>
            <th>Teacher</th>
            <th>Students</th>
        </tr>
        </thead>
        <tbody>
        <% loop $Classes %>
        <tr>
            <td><a href="/school/classes/$ID">$Name</a></td>
            <td><a href="/school/staff/$Staff.ID">$Staff.Name</a></td>
            <td>$Students.Count</td>
        </tr>
        <% end_loop %>
        </tbody>
    </table>
</div>