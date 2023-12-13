$ViewHomework

<% if $Status == 'view' %>

    <%--<% if $CanAdd %>--%>
        <a href="/school/homework/create" class="btn btn-add" style="width: 185px;">Create Homework</a>
    <%--<% end_if %>--%>

    <div class="container-fluid container-homework-playlists create-playlist">

        <div class="row heading-row">
            <div class="col-lg-6" style="border-right: 8px solid white;">
                <div class="row" style="background-color: #f5f6f7; border-bottom: 2px solid darkgray;"><h5>Homework name</h5></div>
            </div>

            <%--<div class="col-lg-1" style="border-right: 8px solid white;">--%>
                <%--<div class="row" style="background-color: #f5f6f7; border-bottom: 2px solid darkgray;"><h4>Tracks</h4></div>--%>
            <%--</div>--%>
            <%--<div class="col-lg-1" style="border-right: 8px solid white;">--%>
                <%--<div class="row" style="background-color: #f5f6f7; border-bottom: 2px solid darkgray;"><h4>Quiz's</h4></div>--%>
            <%--</div>--%>

            <div class="col-lg-3" style="border-right: 8px solid white;">
                <div class="row" style="background-color: #f5f6f7; border-bottom: 2px solid darkgray;"><h5>Assign to</h5></div>
            </div>

            <div class="col-lg-1" style="border-right: 8px solid white;">
                <div class="row" style="background-color: #f5f6f7; border-bottom: 2px solid darkgray;"><h5>Assign</h5></div>
            </div>

            <div class="col-lg-2">
                <div class="row" style="background-color: #f5f6f7; border-bottom: 2px solid darkgray;"><h5>Archive / Publish</h5></div>
            </div>
        </div>

        <% loop $HomeworkPlaylists %>
            <div class="row">
                <div class="col-lg-6" style="border-right: 8px solid white;">
                    <div class="row inner-row row-expand">
                        <div class="col-lg-1"><img src="{$BaseHref}/{$ThemeDir}/images/icons/expand.png"></div>
                        <div class="col-lg-11">$Title</div>
                    </div>
                </div>

                <%--<div class="col-lg-1" style="border-right: 8px solid white;">--%>
                    <%--<div class="row inner-row" style="text-align: center;">$Lessons.Count</div>--%>
                <%--</div>--%>
                <%--<div class="col-lg-1" style="border-right: 8px solid white;">--%>
                    <%--<div class="row inner-row" style="text-align: center;">$Quizzes.Count</div>--%>
                <%--</div>--%>

                <div class="col-lg-3" style="border-right: 8px solid white;">
                    <div class="row inner-row">
                        <%--<div class="col-lg-6"><a href="{$BaseHref}school/homework/edit/{$SchoolClassID}/{$ID}"><img src="{$BaseHref}/{$ThemeDir}/images/icons/edit.png" style="height:20px; float: right;"></a></div>--%>
                        <div class="col-lg-6"><a href="{$BaseHref}school/homework/delete/{$ID}" onclick="return confirm('Are you sure you want to delete this playlist?')"><img src="{$BaseHref}/{$ThemeDir}/images/icons/archive.png" style="height: 20px; float: left;"></a></div>
                    </div>
                </div>

                <div class="col-lg-1" style="border-right: 8px solid white;">
                    <div class="row inner-row" style="text-align: center;">$Lessons.Count</div>
                </div>

                <div class="col-lg-2">
                    <% if $Active %>
                        <a href="{$BaseHref}school/homework/inactive/{$ID}" onclick="return confirm('Are you sure you want to mark this playlist as in-active?')">
                            <div class="row inner-row ">
                                <div class="col-lg-4 col-lg-offset-4 playlist-active">
                                    <img src="{$BaseHref}/{$ThemeDir}/images/icons/unassign.png">
                                </div>
                                <%--<div class="col-lg-8"><p class="unassign"></p></div>--%>
                            </div>
                        </a>
                    <% else %>
                        <a href="{$BaseHref}school/homework/active/{$ID}">
                            <div class="row inner-row playlist-active">
                                <div class="col-lg-4 col-lg-offset-4 playlist-active">
                                    <img src="{$BaseHref}/{$ThemeDir}/images/icons/assign.png" height="20">
                                </div>
                                <%--<div class="col-lg-8"><p class="assign"></p></div>--%>
                            </div>
                        </a>
                    <% end_if %>
                </div>

            </div>

            <%--<div class="container-fluid container-homework-playlist-lessons">--%>
                <%--<% loop $Lessons %>--%>
                    <%--<div class="row">--%>
                        <%--<div class="col-lg-offset-1 col-lg-5" style="border-right: 8px solid white;"><div class="row inner-row"><a href="{$BaseHref}school/homework/lesson/{$ID}/{$Up.SchoolClassID}">$Name</a></div></div>--%>
                        <%--<div class="col-lg-2" style="border-right: 8px solid white;"><div class="row inner-row"></div></div>--%>
                        <%--<div class="col-lg-2" style="border-right: 8px solid white;"><div class="row inner-row"><a href="{$BaseHref}school/homework/lesson/{$ID}/{$Up.SchoolClassID}"><img class="play" src="{$BaseHref}/{$ThemeDir}/images/icons/play.png" height="20"></a></div></div>--%>
                    <%--</div>--%>
                <%--<% end_loop %>--%>
            <%--</div>--%>

        <% end_loop %>
    </div>

<% end_if %>

<% if $Status == 'edit' %>

    <div class="row edit-homework-playlist-row">

        <div class="container-fluid container-edit-homework-playlist override-background">

            <form method="post" action="/school/homework/editPlaylist/{$ID}">

                <%--<input type="hidden" name="SchoolClassID" value="$SchoolClassID">--%>

                <div class="row header-row">
                    <div class="col-lg-6"><input type="text" name="HomeworkPlaylistTitle" value="$HomeworkPlaylist.Title" class="HomeworkPlaylistTitle"></div>
                    <%--<div class="col-lg-3"><a class="btn btn-cancel" style="width:100%;" href="{$BaseHref}/school/homework/{$SchoolClassID}">Cancel</a></div>--%>
                    <div class="col-lg-3"><button type="submit" style="width:100%;" name="submit" value="save" class="btn btn-add homework-playlist-save">Save</button></div>
                    <div class="col-lg-12"><p>Deadline for homework: </p></div>
                </div>

                <div class="row main-row">

                    <div class="col-lg-6 left-col">
                        <div class="row title-row">
                            <div class="col-lg-9"><h3>Tracks</h3></div>
                            <%--<div class="col-lg-3"><a class="btn btn-edit-playlist" href="{$BaseHref}school/homework/edit_tracks/{$SchoolClassID}/{$ID}">Edit Tracks</a></div>--%>
                        </div>
                        <div class="row search-row"><input type="text" class="searchSelectedLessons" data-id="$ID" placeholder="Search by keyword"></div>
                        <div class="row">
                            <div class="selected-lesson-container">
                                <% if $SelectedLessons %>
                                    <% loop $SelectedLessons %>
                                        <div class="col-lg-12 lesson" data-id="$UUID">
                                            <input type="hidden" name="" value="$UUID" class="lesson_input">
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                            <p style="display: inline-block">$Name</p>
                                        </div>
                                    <% end_loop %>
                                <% end_if %>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 right-col">
                        <div class="row title-row">
                            <div class="col-lg-9"><h3>Quiz's</h3></div>
                            <%--<div class="col-lg-3"><a class="btn btn-edit-playlist" href="{$BaseHref}school/homework/edit_quizzes/{$SchoolClassID}/{$ID}">Edit quiz's</a></div></div>--%>
                        <div class="row search-row"><input type="text" class="searchSelectedQuizzes" data-id="$ID" placeholder="Search by keyword"></div>
                        <div class="row">
                            <div class="selected-quiz-container">
                                <% if $SelectedQuizzes %>
                                    <% loop $SelectedQuizzes %>
                                        <div class="col-lg-12 lesson" data-id="$UUID">
                                            <input type="hidden" name="" value="$UUID" class="lesson_input">
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                            <p style="display: inline-block">$Name</p>
                                        </div>
                                    <% end_loop %>
                                <% end_if %>
                            </div>
                        </div>
                    </div>

                </div>

            </form>
        </div>
    </div>

<% end_if %>

<% if $Status == 'editTracks' %>

    <div class="row edit-homework-playlist-row edit">

        <div class="container-fluid container-edit-homework-playlist override-background">

            <form method="post" action="/school/homework/editPlaylist/{$ID}">

                <%--<input type="hidden" name="SchoolClassID" value="$SchoolClassID">--%>

                <div class="row header-row">
                    <div class="col-lg-6"><input type="text" name="HomeworkPlaylistTitle" value="$HomeworkPlaylist.Title" class="HomeworkPlaylistTitle"></div>
                    <%--<div class="col-lg-3"><a class="btn btn-cancel" style="width:100%;" href="{$BaseHref}/school/homework/{$SchoolClassID}">Cancel</a></div>--%>
                    <div class="col-lg-3"><button type="submit" style="width:100%;" name="submit" value="save" class="btn btn-add homework-playlist-save">Save</button></div>
                </div>

                <div class="row main-row">

                    <div class="col-lg-6 left-col">
                        <div class="row title-row"><h3>All Tracks</h3></div>
                        <div class="row search-row"><input type="text" class="searchLessons" data-id="$ID" placeholder="Search by keyword"></div>
                        <div class="row">
                            <div class="lesson-container">
                                <% loop $Lessons %>
                                    <div class="col-lg-12 lesson" data-id="$UUID">
                                        <input type="hidden" value="$UUID" class="lesson_input">
                                        <i class="fa fa-check" aria-hidden="true"></i>
                                        <p style="display: inline-block">$Name</p>
                                    </div>
                                <% end_loop %>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 right-col">
                        <div class="row title-row"><h3>Selected Tracks</h3></div>
                        <div class="row search-row"><input type="text" class="searchSelectedLessons" data-id="$ID" placeholder="Search by keyword"></div>
                        <div class="row">
                            <div class="selected-lesson-container">
                                <% if $SelectedLessons %>
                                    <% loop $SelectedLessons %>
                                        <div class="col-lg-12 lesson" data-id="$UUID">
                                            <input type="hidden" value="$UUID" class="lesson_input">
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                            <p style="display: inline-block">$Name</p>
                                        </div>
                                    <% end_loop %>
                                <% end_if %>
                            </div>
                        </div>
                    </div>

                </div>

            </form>
        </div>
    </div>

<% end_if %>

<% if $Status == 'editQuizzes' %>

    <div class="row edit-homework-playlist-row edit">
        <div class="container-fluid container-edit-homework-playlist override-background">
            <form method="post" action="/school/homework/editPlaylist/{$ID}">

                <%--<input type="hidden" name="SchoolClassID" value="$SchoolClassID">--%>

                <div class="row header-row">
                    <div class="col-lg-6"><input type="text" name="HomeworkPlaylistTitle" value="$HomeworkPlaylist.Title" class="HomeworkPlaylistTitle"></div>
                    <%--<div class="col-lg-3"><a class="btn btn-cancel" style="width:100%;" href="{$BaseHref}/school/homework/{$SchoolClassID}">Cancel</a></div>--%>
                    <div class="col-lg-3"><button type="submit" style="width:100%;" name="submit" value="save" class="btn btn-add homework-playlist-save">Save</button></div>
                </div>

                <div class="row main-row">

                    <div class="col-lg-6 left-col">
                        <div class="row title-row"><h3>All Quiz's</h3></div>
                        <div class="row search-row"><input type="text" class="searchQuizzes" data-id="$ID" placeholder="Search by keyword"></div>
                        <div class="row">
                            <div class="quiz-container">
                                <% if $Quizzes %>
                                    <% loop $Quizzes %>
                                        <div class="col-lg-12 lesson" data-id="$UUID">
                                            <input type="hidden" value="$UUID" class="lesson_input">
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                            <p style="display: inline-block">$Name</p>
                                        </div>
                                    <% end_loop %>
                                <% end_if %>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 right-col">
                        <div class="row title-row"><h3>Selected Quizzes</h3></div>
                        <div class="row search-row"><input type="text" class="searchSelectedQuizzes" data-id="$ID" placeholder="Search by keyword"></div>
                        <div class="row">
                            <div class="selected-quiz-container">
                                <% if $SelectedQuizzes %>
                                    <% loop $SelectedQuizzes %>
                                        <div class="col-lg-12 lesson" data-id="$UUID">
                                            <input type="hidden" value="$UUID" class="lesson_input">
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                            <p style="display: inline-block">$Name</p>
                                        </div>
                                    <% end_loop %>
                                <% end_if %>
                            </div>
                        </div>
                    </div>

                </div>

            </form>
        </div>
    </div>

<% end_if %>

<% if $Status == 'create' %>

    <div class="row create-playlist-row">

        <div class="container-fluid create-playlist override-background edit">
            <%--<form method="post" action="/school/homework/createplaylist/{$SchoolClassID}">--%>

                <%--<input type="hidden" name="SchoolClassID" value="$SchoolClassID">--%>

                <div class="row header-row">
                    <div class="col-lg-6"><input type="text" name="HomeworkPlaylistTitle" placeholder="Homework One" class="HomeworkPlaylistTitle"></div>
                    <div class="col-lg-3"><a class="btn btn-cancel" style="width:100%;" href="{$BaseHref}/school/homework">Cancel</a></div>
                    <div class="col-lg-3"><button type="submit" style="width:100%;" name="submit" value="save" class="btn btn-add homework-playlist-save">Save</button></div>

                    <div class="col-lg-12 homework-deadling" style="padding-top: 25px;">
                        <div class="row">
                            <div class="col-lg-3">
                                <p>Deadline for homework: </p>
                            </div>
                            <div class="col-lg-2">
                                <select name="homework_deadline_month" class="dropdown form-control student-dropdown" style="background-color: #f2f2f2;">
                                    <option class="" value="" <% if $StudentDateFilter == "7" %> selected="selected" <% else %><% end_if %>>Month</option>
                                    <option class="" value="01" <% if $StudentDateFilter == "30" %> selected="selected" <% else %><% end_if %>>January</option>
                                    <option class="" value="02" <% if $StudentDateFilter == "90" %> selected="selected" <% else %><% end_if %>>Feburary</option>
                                </select>
                            </div>
                            <div class="col-lg-1">
                                <select name="homework_deadline_day" class="dropdown form-control student-dropdown" style="background-color: #f2f2f2;">
                                    <option class="" value="" <% if $StudentDateFilter == "7" %> selected="selected" <% else %><% end_if %>>Day</option>
                                    <option class="" value="01" <% if $StudentDateFilter == "30" %> selected="selected" <% else %><% end_if %>>1</option>
                                    <option class="" value="02" <% if $StudentDateFilter == "90" %> selected="selected" <% else %><% end_if %>>2</option>
                                </select>
                            </div>
                            <div class="col-lg-2">
                                <select name="homework_deadline_year" class="dropdown form-control student-dropdown" style="background-color: #f2f2f2;">
                                    <option class="" value="7" <% if $StudentDateFilter == "7" %> selected="selected" <% else %><% end_if %>>Year</option>
                                    <option class="" value="2017" <% if $StudentDateFilter == "30" %> selected="selected" <% else %><% end_if %>>2017</option>
                                    <option class="" value="2018" <% if $StudentDateFilter == "90" %> selected="selected" <% else %><% end_if %>>2018</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row main-row">

                    <div class="col-lg-6 left-col">
                        <div class="row title-row"><h3>All Tracks</h3></div>
                        <div class="row search-row"><input type="text" class="searchLessons" placeholder="Search by keyword"></div>
                        <div class="row">
                            <div class="lesson-container">
                                <% loop $Lessons %>
                                    <div class="row">
                                        <div class="col-lg-12 lesson" data-id="$UUID">
                                            <input type="hidden" value="$UUID" class="lesson_input">
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                            <p>$Name</p>
                                        </div>
                                    </div>
                                <% end_loop %>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6 right-col">
                        <div class="row title-row"><h3>All Quiz's</h3></div>
                        <div class="row search-row"><input type="text" class="searchQuizzes" placeholder="Search by keyword"></div>
                        <div class="row">
                            <div class="quiz-container">
                                <% if $Quizzes %>
                                    <% loop $Quizzes %>
                                        <div class="col-lg-12 lesson" data-id="$UUID">
                                            <input type="hidden" value="$UUID" class="lesson_input">
                                            <i class="fa fa-check" aria-hidden="true"></i>
                                            <p style="display: inline-block">$Name</p>
                                        </div>
                                    <% end_loop %>
                                <% end_if %>
                            </div>
                        </div>
                    </div>

                </div>

            </form>
        </div>
    </div>

<% end_if %>