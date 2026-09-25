<div class="card dm-submit-ticket-card dm-submit-ticket-confirm">
    <div class="card-header">
        <h3 class="card-title mb-0">{lang key="createNewSupportRequest"}</h3>
    </div>
    <div class="card-body">

        <div class="alert alert-success text-center dm-submit-ticket-success">
            <strong>
                {lang key='supportticketsticketcreated'}
                <a id="ticket-number" href="viewticket.php?tid={$tid}&amp;c={$c}" class="alert-link">#{$tid}</a>
            </strong>
        </div>

        <p class="dm-submit-ticket-confirm-message">{lang key='supportticketsticketcreateddesc'}</p>

        <p class="text-center dm-submit-ticket-actions">
            <a href="viewticket.php?tid={$tid}&amp;c={$c}" class="btn btn-default">
                {lang key='continue'}
                <i class="fas fa-arrow-circle-right"></i>
            </a>
        </p>

    </div>
</div>
