<% if $FlashMessageText %>
<div class="container-fluid">
    <div class="row alert alert-$FlashMessageStatus">
        <div class="col-xs-12 message-text">
            $FlashMessageText
        </div>
    </div>
</div>
<% end_if %>