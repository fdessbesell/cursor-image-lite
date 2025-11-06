(function(){
    if(typeof CIL_Settings === 'undefined') return;
    var cursorUrl = CIL_Settings.cursor_url || '';
    var hoverUrl = CIL_Settings.hover_url || '';
    var cursorSize = CIL_Settings.cursor_size || 48;
    var hoverSize = CIL_Settings.hover_size || 48;

    try {
        if(window.matchMedia && (window.matchMedia('(pointer: coarse)').matches || window.matchMedia('(hover: none)').matches)){

            try{ document.documentElement.classList.remove('cil-enabled'); }catch(e){}
            return;
        }
    } catch(e){}

    if(!cursorUrl) return;

    try{ document.documentElement.classList.add('cil-enabled'); }catch(e){}

    var img = document.createElement('img');
    img.className = 'cil-cursor';
    img.src = cursorUrl;
    img.style.width = cursorSize + 'px';
    img.style.height = 'auto';
    img.style.display = 'none';
    document.body.appendChild(img);

    var currentIsHover = false;

    function onMove(e){
        img.style.display = 'block';
        var x = e.clientX;
        var y = e.clientY;

        img.style.transform = 'translate(' + x + 'px, ' + y + 'px) translate(-50%,-50%)';
    }

    function setToHover(){
        if(hoverUrl){
            img.src = hoverUrl;
            img.style.width = hoverSize + 'px';
            currentIsHover = true;
        } else {
            img.src = cursorUrl;
            img.style.width = cursorSize + 'px';
            currentIsHover = false;
        }
    }
    function setToNormal(){
        img.src = cursorUrl;
        img.style.width = cursorSize + 'px';
        currentIsHover = false;
    }

    document.addEventListener('mousemove', onMove, {passive:true});

    document.addEventListener('mouseover', function(e){
        var t = e.target;
        if(t.closest && t.closest('a, button, [role="button"], [onclick]')){
            setToHover();
        } else {
            setToNormal();
        }
    }, true);
    document.addEventListener('mouseout', function(e){

        setToNormal();
    }, true);

    document.addEventListener('mouseleave', function(){ img.style.display = 'none'; });
    document.addEventListener('mouseenter', function(){ img.style.display = 'block'; });
})();