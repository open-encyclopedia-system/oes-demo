$(document).ready(function () {

    /* check for pre-filter (redirect article type pages to article archive) */
    const params = new Proxy(new URLSearchParams(window.location.search), {
        get: (searchParams, prop) => searchParams.get(prop),
    });
    let value = params.filter;
    if(value){
        oesFilterProcessing(value, 't_demo_article_type');
    }
});