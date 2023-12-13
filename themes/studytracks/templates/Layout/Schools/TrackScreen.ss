<div class="table-responsive">
    <table class="table">
        <thead>
        <tr>
            <th>Subject</th>
        </tr>
        </thead>
        <tbody>
            <% loop $Subjects %>
                <tr>
                    <td><a href="/school/tracks/subject/{$UUID}/{$ID}">$Name</a></td>
                </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>