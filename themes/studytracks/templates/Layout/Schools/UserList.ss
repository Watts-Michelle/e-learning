<% if $HelpVideo %>
    <button class="button" onclick="show('teacher-vimeo-video-wrapper')">
        <i class="fa fa-question-circle" style="font-size: 22px;" aria-hidden="true"></i>
    </button>
    <div id="teacher-vimeo-video-wrapper" onclick="hide('teacher-vimeo-video-wrapper')">
        <i class="fa fa-times" id="teacher-vimeo-close" onclick="hide('teacher-vimeo-video-wrapper')" aria-hidden="true"></i>
        <iframe src="{$HelpVideo}?autoplay=1&title=0&byline=0&portrait=0" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
    </div>
<% end_if %>

<% if $School.canAddStaff() %>
    <% if $CanAdd %>
        <a href="/school/staff/create" class="btn btn-add">Add staff member</a>
    <% end_if %>
<% else %>

<div class="container-fluid">
    <div class="row alert alert-warning">
        <div class="col-xs-12 message-text">
            You have reached your school's staff cap, please contact StudyTracks for details of how to increase this.
        </div>
    </div>
</div>
<% end_if %>

<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Last Logged in</th>
                <th>Email</th>
            </tr>
        </thead>
        <tbody>
            <% loop $Users %>
            <tr>
                <td><a href="/school/staff/$ID">$FirstName $Surname</a></td>
                <td>$Role</td>
                <td>$LastVisited.Format('d/m/Y H:i')</td>
                <td><a href="mailto:$Email">$Email</a></td>
            </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>

<script>
    function show(target){
        document.getElementById(target).style.display = 'block';
        document.getElementById("teacher-vimeo-button").style.display = 'none';
    }
    function hide(target){
        document.getElementById(target).style.display = 'none';
        document.getElementById("teacher-vimeo-button").style.display = 'block';
    }
</script>