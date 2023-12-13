<div class="table-responsive">
    <table class="table">
        <thead>
        <tr>
            <th>Lesson</th>
        </tr>
        </thead>
        <tbody>
            <% loop $Lessons %>
                <tr>
                    <td><a href="/school/tracks/lesson/{$ID}">$Name</a></td>
                </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>