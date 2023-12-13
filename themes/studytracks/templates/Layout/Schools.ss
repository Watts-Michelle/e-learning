<div class="row">
    <div class="col-xs-12 col-sm-4 col-md-3 col-lg-2 sidebar">
        <div class="row">
            <div class="col-xs-3 col-sm-12">
                <a href="/school">$School.Logo.SetHeight(160)</a>
            </div>
            <div class="col-xs-5 col-sm-12">
                <h2><a href="/school">$School.Name</a></h2>
            </div>
            <div class="col-xs-4 col-sm-12">
                <ul class="nav nav-sidebar">
                    <% loop $Menu(1) %>
                        <li <% if $Active %>class="active"<% end_if %>><a href="$Link">$Name</a></li>
                    <% end_loop %>
                </ul>
            </div>
        </div>
    </div>
    <% if not $OverrideMain %>
    <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10 main">

        <% include FlashMessage %>
        <% if $Breadcrumbs.count %>
        <ol class="breadcrumb">
            <% loop $Breadcrumbs %>
            <li class="<% if $Active %>active<% end_if %>"><% if $Active %>$Name<% else %><a href="$Link">$Name</a><% end_if %></li>
            <% end_loop %>
        </ol>
        <% end_if %>

        <div class="main-content">
            <% if $PageTitle %>
                <h2>$PageTitle</h2>
            <% end_if %>

            $Content
        </div>
    </div>
    <% else %>
        <div class="col-xs-12 col-sm-8 col-md-9 col-lg-10">
            $Content
        </div>
    <% end_if %>
</div>