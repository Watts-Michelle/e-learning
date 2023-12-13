<% if $HelpVideo %>
    <button class="button" onclick="show('student-vimeo-video-wrapper')">
        <i class="fa fa-question-circle" style="font-size: 22px;" aria-hidden="true"></i>
    </button>
    <div id="student-vimeo-video-wrapper" onclick="hide('student-vimeo-video-wrapper')">
        <i class="fa fa-times" id="student-vimeo-close" onclick="hide('student-vimeo-video-wrapper')" aria-hidden="true"></i>
        <iframe src="{$HelpVideo}?autoplay=1&title=0&byline=0&portrait=0" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
    </div>
<% end_if %>

<form method="post" action="/school/students/filter" style="width: 160px; display:inline-block">
    <select name="filter" onchange="this.form.submit()" class="dropdown form-control student-dropdown">
        <option class="" value="7" <% if $StudentDateFilter == "7" %> selected="selected" <% else %><% end_if %>>Last 7 Days</option>
        <option class="" value="30" <% if $StudentDateFilter == "30" %> selected="selected" <% else %><% end_if %>>Last 30 Days</option>
        <option class="" value="90" <% if $StudentDateFilter == "90" %> selected="selected" <% else %><% end_if %>>Last 90 Days</option>
    </select>
</form>

<% if $School.canAddStudent() %>
    <%--<% if $CanAdd %>--%>
        <%--<a href="/school/homework/$School.ID" class="btn btn-add" style="margin-left: 10px;">Add Homework</a>--%>
    <%--<% end_if %>--%>
    <% if $CanAdd %>
        <a href="/school/students/create" class="btn btn-add">Add student</a>
        $StudentUpload
        <p class="student-upload-spec" onclick="revealSpec('student-upload-specification')">Show specification for importing students!</p>
        <div id="student-upload-specification">
            <p>Students should be uploaded using the following headings for the CSV file.</p>
            <table>
                <tr>
                    <th>First Name</th>
                    <th>Surname</th>
                    <th>Email</th>
                    <th>Class Name</th>
                </tr>
            </table>
            <% if $SiteConfig.StudentImportTemplate %>
                <p>You can download a CSV template to use <a href="$SiteConfig.StudentImportTemplate.Link">here</a>.</p>
            <% end_if %>
        </div>
    <% end_if %>
<% else %>

<div class="container-fluid">
    <div class="row alert alert-warning">
        <div class="col-xs-12 message-text">
            You have reached your school's student cap, please contact StudyTracks for details of how to increase this.
        </div>
    </div>
</div>
<% end_if %>

$ExtraButtons.RAW

<form method="post" action="/school/students/class" id="bulk-change-class-form">
    <% if $BulkSchoolClasses %>
        <button id="bulk-change-class" class="btn btn-class">Change class</button>
        <div class="bulk-change-class">
            <select name="SchoolClass" id="ClassSelector" class="dropdown form-control student-dropdown">
                <% loop $BulkSchoolClasses %>
                    <option name="SchoolClass" value="$ID">$Name</option>
                <% end_loop %>
            </select>
            <button type="submit" name="submit" value="changeclass" class="btn btn-go-upload" id="submit-bulk-change-class">Go</button>
        </div>
    <% end_if %>

    <div class="table-responsive" id="students-table">
        <table class="table">
            <thead>
                <tr>
                    <th class="elem"></th>
                    <th>Name</th>
                    <th>Classes</th>
                    <th>Last Logged in</th>
                    <th>Current Points</th>
                    <th>Completed Lessons</th>
                    <th>Completed Quiz's</th>
                    <th>Quiz Score Average</th>
                    <th>Points Earned</th>
                </tr>
            </thead>
            <tbody>
                <% loop $Students %>
                <tr>
                    <td class="elem"><input type="checkbox" name="StudentID[]" value="$ID" /></td>
                    <td><a href="/school/students/$UUID">$FirstName $Surname</a></td>
                    <td>
                        <% if $SchoolClasses %>
                            <% loop $SchoolClasses %>
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
                    </td>
                    <td>$LastAccess.Format('d/m/Y H:i')</td>
                    <td>$TotalPoints</td>
                    <td>$FilterCompletedLessons</td>
                    <td>$FilterMemberQuizSessions</td>
                    <td>$FilterPercentageCorrect</td>
                    <td>$FilterTotalPoints</td>
                </tr>
                <% end_loop %>
            </tbody>
        </table>
    </div>
</form>

<script>
    function show(target){
        document.getElementById(target).style.display = 'block';
        document.getElementById("student-vimeo-button").style.display = 'none';
    }
    function hide(target){
        document.getElementById(target).style.display = 'none';
        document.getElementById("student-vimeo-button").style.display = 'block';
    }
    function revealSpec(){
        jQuery(document).ready(function($) {
            $('#student-upload-specification').slideToggle();
        });
    }
</script>