<% if $Quizzes %>
    <% loop $Quizzes %>
        <div class="col-lg-12 lesson" data-id="$UUID">
            <input type="hidden" value="$ID" class="lesson_input">
            <i class="fa fa-check" aria-hidden="true"></i>
            <p style="display: inline-block">$Name</p>
        </div>
    <% end_loop %>
<% end_if %>