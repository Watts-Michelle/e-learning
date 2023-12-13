<div class="table-responsive">
    <table class="table">
        <thead>
        <tr>
            <th>Lesson Content</th>
        </tr>
        </thead>
        <tbody>
            <% loop $Lesson %>
            <tr>
                <% if $Content %>
                    <td>$Content</td>
                <% else %>
                    <td>There is no content for this lesson.</td>
                <% end_if %>
            </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>