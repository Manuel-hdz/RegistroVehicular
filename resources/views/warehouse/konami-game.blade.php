<style>
    .konami-game-overlay { position:fixed; inset:0; z-index:2000; display:none; place-items:center; padding:16px; background:rgba(4,7,18,.96); }
    .konami-game-overlay.is-open { display:grid; }
    .konami-game-layout { display:grid; grid-template-columns:220px minmax(0, 470px); gap:18px; align-items:start; width:min(100%, 708px); }
    .konami-game-layout.is-tetris { grid-template-columns:minmax(0, 470px); width:min(100%, 470px); }
    .konami-game-shell { width:100%; color:#fff; }
    .konami-game-picker { width:min(100%, 620px); padding:28px; border:1px solid #314cc8; border-radius:10px; color:#fff; background:#10152b; text-align:center; }
    .konami-game-picker h2 { margin:0 0 8px; color:#ffd928; }
    .konami-game-picker p { margin:0 0 22px; color:#cbd3ff; }
    .konami-game-options { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:14px; }
    .konami-game-option { min-height:130px; padding:18px; border:2px solid #314cc8; border-radius:10px; color:#fff; background:#19203d; font-weight:900; font-size:1.15rem; }
    .konami-game-option:hover, .konami-game-option:focus-visible { border-color:#ffd928; color:#ffd928; transform:translateY(-2px); }
    .konami-game-option i { display:block; margin-bottom:10px; font-size:2.2rem; }
    .konami-leaderboard { padding:14px; border:1px solid #314cc8; border-radius:6px; color:#fff; background:#10152b; }
    .konami-leaderboard h3 { margin:0 0 10px; color:#ffd928; font-size:1rem; }
    .konami-score-list { display:grid; gap:7px; margin:0; padding:0; list-style:none; }
    .konami-score-list li { display:grid; grid-template-columns:24px minmax(0,1fr) auto; gap:6px; align-items:center; font-size:.82rem; }
    .konami-score-list strong { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
    .konami-score-list span:last-child { color:#ffd928; font-weight:900; }
    @media (max-width:760px) { .konami-game-layout { grid-template-columns:minmax(0,1fr); max-height:calc(100vh - 24px); overflow-y:auto; } .konami-leaderboard { order:2; } }
    .konami-game-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:10px; }
    .konami-game-title { margin:0; color:#ffd928; font-size:1.25rem; font-weight:900; }
    .konami-game-stats { display:flex; gap:14px; font-weight:800; }
    .konami-game-actions { display:flex; gap:8px; }
    .konami-switch-btn { min-height:40px; padding:0 12px; border:1px solid #46527c; border-radius:6px; color:#fff; background:#19203d; font-weight:800; white-space:nowrap; }
    .konami-difficulty { display:grid; grid-template-columns:repeat(3, 1fr); gap:6px; margin:0 0 10px; }
    .konami-difficulty button { min-height:38px; border:1px solid #46527c; border-radius:6px; color:#fff; background:#19203d; font-weight:800; }
    .konami-difficulty button.is-active { border-color:#ffd928; color:#10152b; background:#ffd928; }
    .konami-icon-btn { display:grid; place-items:center; width:40px; height:40px; padding:0; border:1px solid #46527c; border-radius:6px; color:#fff; background:#19203d; }
    .konami-canvas { display:block; width:100%; height:auto; border:2px solid #314cc8; background:#02030b; image-rendering:pixelated; }
    #konamiPacmanCanvas { aspect-ratio:19/15; }
    #konamiTetrisCanvas { width:min(100%, 300px); margin:auto; aspect-ratio:1/2; }
    .konami-game-message { min-height:28px; margin:8px 0 0; color:#ffd928; text-align:center; font-weight:900; }
    .konami-control-help { margin:8px 0 0; color:#cbd3ff; text-align:center; font-size:.82rem; }
    .konami-dpad { display:grid; grid-template-columns:repeat(3, 48px); grid-template-rows:repeat(2, 44px); justify-content:center; gap:6px; margin-top:10px; }
    .konami-dpad button { display:grid; place-items:center; padding:0; border:1px solid #46527c; border-radius:6px; color:#fff; background:#19203d; touch-action:manipulation; }
    .konami-dpad [data-direction="up"] { grid-column:2; }
    .konami-dpad [data-direction="left"] { grid-column:1; grid-row:2; }
    .konami-dpad [data-direction="down"] { grid-column:2; grid-row:2; }
    .konami-dpad [data-direction="right"] { grid-column:3; grid-row:2; }
    .konami-back { margin-bottom:10px; border:0; color:#cbd3ff; background:transparent; font-weight:800; }
    .konami-hidden { display:none !important; }
    @media (max-width:520px) { .konami-game-options { grid-template-columns:1fr; } .konami-game-picker { padding:20px; } .konami-game-bar { align-items:flex-start; } .konami-game-actions { flex-wrap:wrap; justify-content:flex-end; } .konami-switch-btn { width:100%; order:3; } }
</style>

<div id="konamiGameOverlay" class="konami-game-overlay" role="dialog" aria-modal="true" aria-labelledby="konamiGameTitle" aria-hidden="true">
    <section id="konamiGamePicker" class="konami-game-picker">
        <h2>Elige un juego</h2>
        <p>Has desbloqueado el men&uacute; secreto. Tecla de p&aacute;nico: <strong>Esc</strong>.</p>
        <div class="konami-game-options">
            <button type="button" class="konami-game-option" data-game="pacman"><i class="bi bi-emoji-smile"></i>PAC-MAN</button>
            <button type="button" class="konami-game-option" data-game="tetris"><i class="bi bi-grid-3x3-gap-fill"></i>Tetris</button>
        </div>
        <button id="konamiPickerClose" class="konami-icon-btn" type="button" title="Cerrar" aria-label="Cerrar" style="margin:20px auto 0"><i class="bi bi-x-lg"></i></button>
    </section>
    <div id="konamiGameLayout" class="konami-game-layout konami-hidden">
        <aside id="konamiLeaderboard" class="konami-leaderboard" aria-labelledby="konamiLeaderboardTitle">
            <h3 id="konamiLeaderboardTitle">Puntajes m&aacute;s altos</h3>
            <ol id="konamiScoreList" class="konami-score-list">
                <li><span></span><strong>Cargando...</strong><span></span></li>
            </ol>
        </aside>
        <div class="konami-game-shell">
        <button id="konamiBack" class="konami-back" type="button"><i class="bi bi-chevron-left"></i> Cambiar juego</button>
        <div class="konami-game-bar">
            <div>
                <h2 id="konamiGameTitle" class="konami-game-title">PAC-MAN</h2>
                <div class="konami-game-stats"><span>Puntos: <b id="konamiScore">0</b></span><span id="konamiLivesStat">Vidas: <b id="konamiLives">3</b></span><span id="konamiLinesStat" class="konami-hidden">L&iacute;neas: <b id="konamiLines">0</b></span></div>
            </div>
            <div class="konami-game-actions">
                <button id="konamiSwitchGame" class="konami-switch-btn" type="button" title="Cambiar de juego">Ir a Tetris</button>
                <button id="konamiRestart" class="konami-icon-btn" type="button" title="Reiniciar" aria-label="Reiniciar"><i class="bi bi-arrow-clockwise"></i></button>
                <button id="konamiClose" class="konami-icon-btn" type="button" title="Cerrar" aria-label="Cerrar"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
        <div class="konami-difficulty" role="group" aria-label="Dificultad">
            <button type="button" data-difficulty="easy">F&aacute;cil</button>
            <button type="button" data-difficulty="normal" class="is-active">Normal</button>
            <button type="button" data-difficulty="hard">Dif&iacute;cil</button>
        </div>
        <canvas id="konamiPacmanCanvas" class="konami-canvas" width="380" height="300"></canvas>
        <canvas id="konamiTetrisCanvas" class="konami-canvas konami-hidden" width="300" height="600"></canvas>
        <div id="konamiGameMessage" class="konami-game-message" aria-live="polite"></div>
        <div id="konamiControlHelp" class="konami-control-help">Usa las flechas para moverte.</div>
        <div class="konami-dpad" aria-label="Controles de direcci&oacute;n">
            <button type="button" data-direction="up" title="Arriba" aria-label="Arriba"><i class="bi bi-chevron-up"></i></button>
            <button type="button" data-direction="left" title="Izquierda" aria-label="Izquierda"><i class="bi bi-chevron-left"></i></button>
            <button type="button" data-direction="down" title="Abajo" aria-label="Abajo"><i class="bi bi-chevron-down"></i></button>
            <button type="button" data-direction="right" title="Derecha" aria-label="Derecha"><i class="bi bi-chevron-right"></i></button>
        </div>
        </div>
    </div>
</div>

<script>
(function(){
    const sequence = [38,38,40,40,37,39,37,39,66,65];
    let sequenceIndex = 0;
    const overlay = document.getElementById('konamiGameOverlay');
    const picker = document.getElementById('konamiGamePicker');
    const gameLayout = document.getElementById('konamiGameLayout');
    const canvas = document.getElementById('konamiPacmanCanvas');
    const ctx = canvas.getContext('2d');
    const tetrisCanvas = document.getElementById('konamiTetrisCanvas');
    const tetrisCtx = tetrisCanvas.getContext('2d');
    const gameTitle = document.getElementById('konamiGameTitle');
    const leaderboard = document.getElementById('konamiLeaderboard');
    const livesStat = document.getElementById('konamiLivesStat');
    const linesStat = document.getElementById('konamiLinesStat');
    const linesNode = document.getElementById('konamiLines');
    const controlHelp = document.getElementById('konamiControlHelp');
    const scoreNode = document.getElementById('konamiScore');
    const livesNode = document.getElementById('konamiLives');
    const messageNode = document.getElementById('konamiGameMessage');
    const scoreListNode = document.getElementById('konamiScoreList');
    const scoresUrl = @json(route('warehouse.pacman-scores.index'));
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const tile = 20;
    const maze = [
        '###################',
        '#........#........#',
        '#.###.##.#.##.###.#',
        '#.................#',
        '#.##.#.#####.#.##.#',
        '#....#...#...#....#',
        '####.### # ###.####',
        '   #.#       #.#   ',
        '####.# ## ## #.####',
        '#......#   #......#',
        '#.##.#.#####.#.##.#',
        '#..#...........#..#',
        '##.#.##.#.#.##.#.##',
        '#........#........#',
        '###################'
    ];
    const directions = { up:{x:0,y:-1}, down:{x:0,y:1}, left:{x:-1,y:0}, right:{x:1,y:0} };
    const speeds = { easy:210, normal:150, hard:95 };
    const superDotPositions = ['1,3','17,3','1,13','17,13'];
    let player, ghosts, dots, superDots, direction, queuedDirection, score, lives, timer, frightenedUntil, difficulty = 'normal', scoreSaved = false, mouth = 0;
    let selectedGame = null;
    let tetrisBoard, tetrisPiece, tetrisTimer, tetrisScore = 0, tetrisLines = 0;
    const tetrisColumns = 10;
    const tetrisRows = 20;
    const tetrisBlock = 30;
    const tetrisSpeeds = { easy:700, normal:450, hard:250 };
    const tetrisPieces = [
        {color:'#22d3ee', shape:[[1,1,1,1]]},
        {color:'#facc15', shape:[[1,1],[1,1]]},
        {color:'#a855f7', shape:[[0,1,0],[1,1,1]]},
        {color:'#22c55e', shape:[[0,1,1],[1,1,0]]},
        {color:'#ef4444', shape:[[1,1,0],[0,1,1]]},
        {color:'#3b82f6', shape:[[1,0,0],[1,1,1]]},
        {color:'#f97316', shape:[[0,0,1],[1,1,1]]}
    ];

    function renderScores(scores){
        const difficultyLabels={easy:'F\u00e1cil',normal:'Normal',hard:'Dif\u00edcil'};
        scoreListNode.innerHTML='';
        if(!scores.length){
            scoreListNode.innerHTML='<li><span></span><strong>Sin puntajes</strong><span></span></li>';
            return;
        }
        scores.forEach(function(item,index){
            const row=document.createElement('li');
            const position=document.createElement('span');
            const name=document.createElement('strong');
            const points=document.createElement('span');
            position.textContent=(index+1)+'.';
            name.textContent=item.name+' - '+(difficultyLabels[item.difficulty] || item.difficulty);
            points.textContent=item.score;
            row.append(position,name,points);
            scoreListNode.appendChild(row);
        });
    }
    function loadScores(){
        fetch(scoresUrl,{headers:{Accept:'application/json'}})
            .then(response=>response.json())
            .then(data=>renderScores(data.scores || []))
            .catch(()=>{ scoreListNode.innerHTML='<li><span></span><strong>No disponible</strong><span></span></li>'; });
    }
    function saveScore(){
        if(scoreSaved) return;
        scoreSaved=true;
        fetch(scoresUrl,{
            method:'POST',
            headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrfToken},
            body:JSON.stringify({score:score,difficulty:difficulty})
        }).then(response=>response.json()).then(data=>renderScores(data.scores || [])).catch(()=>{ scoreSaved=false; });
    }
    function finishGame(message){ messageNode.textContent=message; stop(); saveScore(); }

    function key(x,y){ return x+','+y; }
    function isWall(x,y){ return y < 0 || y >= maze.length || x < 0 || x >= maze[0].length || maze[y][x] === '#'; }
    function resetPositions(){
        player = {x:1,y:1};
        ghosts = [{x:9,y:7,color:'#ef3340'},{x:8,y:7,color:'#ff8fce'},{x:10,y:7,color:'#20d5e8'}];
        direction = directions.right;
        queuedDirection = directions.right;
    }
    function resetGame(){
        dots = new Set();
        maze.forEach((row,y)=> [...row].forEach((cell,x)=> { if(cell === '.') dots.add(key(x,y)); }));
        superDots = new Set(superDotPositions.filter(position => dots.has(position)));
        score = 0; lives = 3; frightenedUntil = 0; scoreSaved = false; messageNode.textContent = '';
        resetPositions(); updateStats(); draw();
    }
    function updateStats(){ scoreNode.textContent=score; livesNode.textContent=lives; }
    function canMove(entity,dir){ return !isWall(entity.x+dir.x,entity.y+dir.y); }
    function movePlayer(){
        if(canMove(player,queuedDirection)) direction=queuedDirection;
        if(canMove(player,direction)){ player.x+=direction.x; player.y+=direction.y; }
        const dotKey=key(player.x,player.y);
        if(superDots.delete(dotKey)){
            dots.delete(dotKey);
            score+=50;
            frightenedUntil=Date.now()+6000;
            updateStats();
        } else if(dots.delete(dotKey)){ score+=10; updateStats(); }
        if(!dots.size){ finishGame('Ganaste!'); }
    }
    function moveGhost(ghost){
        const choices=Object.values(directions).filter(dir=>canMove(ghost,dir));
        choices.sort((a,b)=>{
            const da=Math.abs(ghost.x+a.x-player.x)+Math.abs(ghost.y+a.y-player.y)+Math.random()*3;
            const db=Math.abs(ghost.x+b.x-player.x)+Math.abs(ghost.y+b.y-player.y)+Math.random()*3;
            return da-db;
        });
        if(choices[0]){ ghost.x+=choices[0].x; ghost.y+=choices[0].y; }
    }
    function checkCollision(){
        const ghostIndex=ghosts.findIndex(ghost=>ghost.x===player.x && ghost.y===player.y);
        if(ghostIndex<0) return;
        if(Date.now()<frightenedUntil){
            const homes=[{x:9,y:7},{x:8,y:7},{x:10,y:7}];
            ghosts[ghostIndex].x=homes[ghostIndex].x;
            ghosts[ghostIndex].y=homes[ghostIndex].y;
            score+=200; updateStats();
            return;
        }
        lives--; updateStats();
        if(lives<=0){ finishGame('Fin del juego'); return; }
        resetPositions();
    }
    function draw(){
        ctx.clearRect(0,0,canvas.width,canvas.height);
        maze.forEach((row,y)=> [...row].forEach((cell,x)=>{
            if(cell==='#'){
                ctx.fillStyle='#183aaf'; ctx.fillRect(x*tile,y*tile,tile,tile);
                ctx.strokeStyle='#4a68ff'; ctx.strokeRect(x*tile+2,y*tile+2,tile-4,tile-4);
            } else if(dots.has(key(x,y))){
                const isSuper=superDots.has(key(x,y));
                ctx.fillStyle='#ffe8b0'; ctx.beginPath(); ctx.arc(x*tile+10,y*tile+10,isSuper?6:2.5,0,Math.PI*2); ctx.fill();
            }
        }));
        const angle=Math.atan2(direction.y,direction.x); mouth=(mouth+1)%2;
        ctx.fillStyle='#ffd928'; ctx.beginPath(); ctx.moveTo(player.x*tile+10,player.y*tile+10); ctx.arc(player.x*tile+10,player.y*tile+10,8,angle+(mouth?.28:.08),angle+Math.PI*2-(mouth?.28:.08)); ctx.closePath(); ctx.fill();
        ghosts.forEach(ghost=>{
            const cx=ghost.x*tile+10, cy=ghost.y*tile+10;
            ctx.fillStyle=Date.now()<frightenedUntil ? '#244cff' : ghost.color; ctx.beginPath(); ctx.arc(cx,cy,8,Math.PI,0); ctx.lineTo(cx+8,cy+8); ctx.lineTo(cx+3,cy+5); ctx.lineTo(cx,cy+8); ctx.lineTo(cx-3,cy+5); ctx.lineTo(cx-8,cy+8); ctx.closePath(); ctx.fill();
            ctx.fillStyle='#fff'; ctx.beginPath(); ctx.arc(cx-3,cy-2,2,0,Math.PI*2); ctx.arc(cx+3,cy-2,2,0,Math.PI*2); ctx.fill();
        });
    }
    function tick(){ movePlayer(); ghosts.forEach(moveGhost); checkCollision(); draw(); }
    function start(){ stop(); timer=setInterval(tick,speeds[difficulty]); }
    function stop(){ if(timer){clearInterval(timer);timer=null;} }

    function createTetrisBoard(){ return Array.from({length:tetrisRows},()=>Array(tetrisColumns).fill(null)); }
    function createTetrisPiece(){
        const template=tetrisPieces[Math.floor(Math.random()*tetrisPieces.length)];
        return {shape:template.shape.map(row=>row.slice()),color:template.color,x:Math.floor((tetrisColumns-template.shape[0].length)/2),y:0};
    }
    function tetrisCollides(piece=tetrisPiece){
        return piece.shape.some((row,y)=>row.some((cell,x)=>{
            if(!cell) return false;
            const boardX=piece.x+x, boardY=piece.y+y;
            return boardX<0 || boardX>=tetrisColumns || boardY>=tetrisRows || (boardY>=0 && tetrisBoard[boardY][boardX]);
        }));
    }
    function drawTetrisCell(x,y,color){
        tetrisCtx.fillStyle=color;
        tetrisCtx.fillRect(x*tetrisBlock+1,y*tetrisBlock+1,tetrisBlock-2,tetrisBlock-2);
        tetrisCtx.strokeStyle='rgba(255,255,255,.25)';
        tetrisCtx.strokeRect(x*tetrisBlock+2,y*tetrisBlock+2,tetrisBlock-4,tetrisBlock-4);
    }
    function drawTetris(){
        tetrisCtx.fillStyle='#02030b';
        tetrisCtx.fillRect(0,0,tetrisCanvas.width,tetrisCanvas.height);
        tetrisCtx.strokeStyle='rgba(70,82,124,.18)';
        for(let x=0;x<=tetrisColumns;x++){ tetrisCtx.beginPath(); tetrisCtx.moveTo(x*tetrisBlock,0); tetrisCtx.lineTo(x*tetrisBlock,tetrisCanvas.height); tetrisCtx.stroke(); }
        for(let y=0;y<=tetrisRows;y++){ tetrisCtx.beginPath(); tetrisCtx.moveTo(0,y*tetrisBlock); tetrisCtx.lineTo(tetrisCanvas.width,y*tetrisBlock); tetrisCtx.stroke(); }
        tetrisBoard.forEach((row,y)=>row.forEach((color,x)=>{ if(color) drawTetrisCell(x,y,color); }));
        if(tetrisPiece) tetrisPiece.shape.forEach((row,y)=>row.forEach((cell,x)=>{ if(cell && tetrisPiece.y+y>=0) drawTetrisCell(tetrisPiece.x+x,tetrisPiece.y+y,tetrisPiece.color); }));
    }
    function updateTetrisStats(){ scoreNode.textContent=tetrisScore; linesNode.textContent=tetrisLines; }
    function clearTetrisLines(){
        let cleared=0;
        for(let y=tetrisRows-1;y>=0;y--){
            if(tetrisBoard[y].every(Boolean)){ tetrisBoard.splice(y,1); tetrisBoard.unshift(Array(tetrisColumns).fill(null)); cleared++; y++; }
        }
        if(cleared){ tetrisLines+=cleared; tetrisScore+=[0,100,300,500,800][cleared]; updateTetrisStats(); }
    }
    function lockTetrisPiece(){
        tetrisPiece.shape.forEach((row,y)=>row.forEach((cell,x)=>{ if(cell && tetrisPiece.y+y>=0) tetrisBoard[tetrisPiece.y+y][tetrisPiece.x+x]=tetrisPiece.color; }));
        clearTetrisLines();
        tetrisPiece=createTetrisPiece();
        if(tetrisCollides()){ stopTetris(); messageNode.textContent='Fin del juego'; tetrisPiece=null; }
    }
    function moveTetris(dx,dy){
        if(!tetrisPiece) return false;
        const moved={...tetrisPiece,x:tetrisPiece.x+dx,y:tetrisPiece.y+dy};
        if(tetrisCollides(moved)) return false;
        tetrisPiece=moved; drawTetris(); return true;
    }
    function rotateTetris(){
        if(!tetrisPiece) return;
        const shape=tetrisPiece.shape[0].map((_,x)=>tetrisPiece.shape.map(row=>row[x]).reverse());
        for(const offset of [0,-1,1,-2,2]){
            const rotated={...tetrisPiece,shape:shape,x:tetrisPiece.x+offset};
            if(!tetrisCollides(rotated)){ tetrisPiece=rotated; drawTetris(); return; }
        }
    }
    function dropTetris(){
        if(!moveTetris(0,1)){ lockTetrisPiece(); drawTetris(); }
    }
    function hardDropTetris(){
        if(!tetrisPiece) return;
        let distance=0;
        while(moveTetris(0,1)) distance++;
        tetrisScore+=distance*2; updateTetrisStats(); lockTetrisPiece(); drawTetris();
    }
    function resetTetris(){
        stopTetris(); tetrisBoard=createTetrisBoard(); tetrisPiece=createTetrisPiece(); tetrisScore=0; tetrisLines=0;
        messageNode.textContent=''; updateTetrisStats(); drawTetris();
    }
    function startTetris(){ stopTetris(); tetrisTimer=setInterval(dropTetris,tetrisSpeeds[difficulty]); }
    function stopTetris(){ if(tetrisTimer){ clearInterval(tetrisTimer); tetrisTimer=null; } }
    function showPicker(){
        stop(); stopTetris(); selectedGame=null;
        picker.classList.remove('konami-hidden'); gameLayout.classList.add('konami-hidden');
        picker.querySelector('[data-game]').focus();
    }
    function selectGame(game){
        stop(); stopTetris();
        selectedGame=game; picker.classList.add('konami-hidden'); gameLayout.classList.remove('konami-hidden');
        const isTetris=game==='tetris';
        gameLayout.classList.toggle('is-tetris',isTetris);
        gameTitle.textContent=isTetris ? 'TETRIS' : 'PAC-MAN';
        document.getElementById('konamiSwitchGame').textContent=isTetris ? 'Ir a PAC-MAN' : 'Ir a Tetris';
        controlHelp.textContent=isTetris
            ? 'Flechas: mover y girar. Barra espaciadora: ca\u00edda r\u00e1pida.'
            : 'Usa las flechas para moverte.';
        canvas.classList.toggle('konami-hidden',isTetris);
        tetrisCanvas.classList.toggle('konami-hidden',!isTetris);
        leaderboard.classList.toggle('konami-hidden',isTetris);
        livesStat.classList.toggle('konami-hidden',isTetris);
        linesStat.classList.toggle('konami-hidden',!isTetris);
        if(isTetris){ resetTetris(); startTetris(); } else { resetGame(); loadScores(); start(); }
        document.getElementById('konamiClose').focus();
    }
    function openGame(){ overlay.classList.add('is-open'); overlay.setAttribute('aria-hidden','false'); showPicker(); }
    function closeGame(){ stop(); stopTetris(); overlay.classList.remove('is-open'); overlay.setAttribute('aria-hidden','true'); }
    function setDirection(name){
        if(selectedGame==='tetris'){
            if(name==='left') moveTetris(-1,0);
            if(name==='right') moveTetris(1,0);
            if(name==='down') dropTetris();
            if(name==='up') rotateTetris();
            return;
        }
        queuedDirection=directions[name];
    }

    window.addEventListener('keydown',function(event){
        if(overlay.classList.contains('is-open')){
            const map={ArrowUp:'up',ArrowDown:'down',ArrowLeft:'left',ArrowRight:'right'};
            if(selectedGame && map[event.key]){ event.preventDefault(); setDirection(map[event.key]); }
            if(selectedGame==='tetris' && event.code==='Space'){ event.preventDefault(); hardDropTetris(); }
            if(event.key==='Escape') closeGame();
            return;
        }

        const pressed = event.keyCode || event.which;
        sequenceIndex = pressed===sequence[sequenceIndex] ? sequenceIndex+1 : (pressed===sequence[0] ? 1 : 0);
        if(sequenceIndex===sequence.length){
            sequenceIndex=0;
            event.preventDefault();
            openGame();
        }
    }, true);
    document.getElementById('konamiClose').addEventListener('click',closeGame);
    document.getElementById('konamiPickerClose').addEventListener('click',closeGame);
    document.getElementById('konamiBack').addEventListener('click',showPicker);
    document.getElementById('konamiSwitchGame').addEventListener('click',function(){
        selectGame(selectedGame==='tetris' ? 'pacman' : 'tetris');
    });
    picker.querySelectorAll('[data-game]').forEach(button=>button.addEventListener('click',()=>selectGame(button.dataset.game)));
    document.getElementById('konamiRestart').addEventListener('click',function(){
        if(selectedGame==='tetris'){ resetTetris(); startTetris(); }
        else { resetGame(); start(); }
    });
    overlay.querySelectorAll('[data-difficulty]').forEach(function(button){
        button.addEventListener('click',function(){
            difficulty=button.dataset.difficulty;
            overlay.querySelectorAll('[data-difficulty]').forEach(item=>item.classList.toggle('is-active',item===button));
            if(selectedGame==='tetris' && tetrisTimer) startTetris();
            if(selectedGame==='pacman' && timer) start();
        });
    });
    overlay.querySelectorAll('[data-direction]').forEach(button=>button.addEventListener('pointerdown',()=>setDirection(button.dataset.direction)));
})();
</script>
