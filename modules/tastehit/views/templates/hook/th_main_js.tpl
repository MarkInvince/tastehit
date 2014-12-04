<script>
    var _thq = [];
    _thq.push(['setId', '{$th_customer_id}']);
    _thq.push(['setThUrl', '{$th_url}']);
    _thq.push(['trackPageView']);
    _thq.push(['trackEvents']);

    _thq.push(['displayWidget']);

    (function () {
        var th = document.createElement('script');
        th.type = 'text/javascript';
        th.async = true;
        th.src = 'https://www.tastehit.com/static/th.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(th, s);
    })();
</script>