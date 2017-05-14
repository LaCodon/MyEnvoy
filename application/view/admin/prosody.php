<div class="row">
    <div class="col s12">
        <h1>Prosody IM</h1>
    </div>
</div>
<div class="row">
    <div class="col s12 m6">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Uptime</span>
                The Prosody IM server is up and running since <?php echo ($this->uptime instanceof DateTime ? $this->uptime->format('H:i d.m.Y') : $this->uptime); ?>.
            </div>
        </div>
    </div>
    <div class="col s12 m3">
        <div class="card">
            <div class="card-content">
                <span class="card-title">Online users</span>
                <div class="user-count"><?php echo $this->onlineUsers; ?></div>
            </div>
        </div>
    </div>
</div>