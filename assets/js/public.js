(function(){
    if(typeof CURSIMLI_Settings === 'undefined') return;
    
    if(window.matchMedia('(hover: none)').matches || window.matchMedia('(pointer: coarse)').matches) return;
    
    var cursorUrl = CURSIMLI_Settings.cursor_url || '';
    var hoverUrl = CURSIMLI_Settings.hover_url || '';
    var cursorSize = CURSIMLI_Settings.cursor_size || 48;
    var hoverSize = CURSIMLI_Settings.hover_size || 48;

    if (!cursorUrl && !hoverUrl) return;

    if (!cursorUrl) return;

    try{ document.documentElement.classList.add('cursimli-enabled'); }catch(e){}
    var img = document.createElement('img');
    img.className = 'cursimli-cursor';
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
        if(!hoverUrl) return;
        if(currentIsHover) return;
        currentIsHover = true;
        img.src = hoverUrl;
        img.style.width = hoverSize + 'px';
    }
    function setToNormal(){
        if(!cursorUrl) return;
        if(!currentIsHover) return;
        currentIsHover = false;
        img.src = cursorUrl;
        img.style.width = cursorSize + 'px';
    }

    document.addEventListener('mousemove', onMove, {passive:true});

    document.addEventListener('mouseover', function(e){
        var t = e.target;
        while(t && t !== document){
            if(t.tagName && (t.tagName.toLowerCase() === 'a' || t.tagName.toLowerCase() === 'button' || t.getAttribute && t.getAttribute('role') === 'button')){
                setToHover();
                return;
            }
            t = t.parentNode;
        }
    }, true);

    document.addEventListener('mouseout', function(e){
        var related = e.relatedTarget;
        if(!related){
            setToNormal();
            return;
        }
        var t = related;
        while(t && t !== document){
            if(t.tagName && (t.tagName.toLowerCase() === 'a' || t.tagName.toLowerCase() === 'button' || t.getAttribute && t.getAttribute('role') === 'button')){
                return;
            }
            t = t.parentNode;
        }
        setToNormal();
    }, true);

    document.addEventListener('mouseleave', function(){ img.style.display = 'none'; });
    document.addEventListener('mouseenter', function(){ img.style.display = 'block'; });
})();