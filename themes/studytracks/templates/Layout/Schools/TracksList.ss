<div class="table-responsive">
    <table class="table">
        <thead>
        <tr>
            <th>Name</th>
        </tr>
        </thead>
        <tbody>
            <% loop $Tracks %>
                <tr>
                    <td><a href="/school/tracks/$ID">$Name </a></td>
                </tr>
            <% end_loop %>
        </tbody>
    </table>
</div>