document.addEventListener('DOMContentLoaded', function() {
    const chapters = document.querySelectorAll('.chapter');
    const episodes = document.querySelectorAll('.episode');
    const contentDisplay = document.getElementById('content-display');
    const prevBtn = document.getElementById('prev-btn');
    const nextBtn = document.getElementById('next-btn');
    const completeBtn = document.getElementById('complete-btn');
    const bookmarkBtn = document.getElementById('bookmark-btn');
    const downloadBtn = document.getElementById('download-btn');
    const sidebar = document.getElementById('sidebar');
    const resizer = document.getElementById('sidebar-resizer');
    const sidebarCloseBtn = document.getElementById('sidebar-close-btn');

    let watermarkIntervalId = null;
    let watermarkHideTimeoutId = null;

    let currentEpisodeIndex = 0;
    const allEpisodes = Array.from(episodes);

    function tryStartWatermarkLoop(container) {
        if (!window.rsLmsRest || !rsLmsRest.userEmail) return;
        // Ensure container is positioning context
        if (getComputedStyle(container).position === 'static') {
            container.style.position = 'relative';
        }
        const showOnce = () => {
            // Create or reuse watermark element
            let wm = document.getElementById('rs-lms-watermark');
            if (!wm) {
                wm = document.createElement('div');
                wm.id = 'rs-lms-watermark';
                wm.style.position = 'absolute';
                wm.style.zIndex = '50';
                wm.style.pointerEvents = 'none';
                wm.style.background = 'rgba(0,0,0,0.3)';
                wm.style.color = 'white';
                wm.style.padding = '6px 10px';
                wm.style.borderRadius = '6px';
                wm.style.fontSize = '12px';
                wm.style.transition = 'opacity 300ms ease';
                wm.style.opacity = '0';
                wm.textContent = atob(rsLmsRest.userEmail);
                container.appendChild(wm);
            }
            // Random corner
            const corners = ['top-left', 'top-right', 'bottom-left', 'bottom-right'];
            const pick = corners[Math.floor(Math.random() * corners.length)];
            // Reset all anchors
            wm.style.top = 'auto';
            wm.style.right = 'auto';
            wm.style.bottom = 'auto';
            wm.style.left = 'auto';
            const margin = Math.random() * 100;
            switch (pick) {
                case 'top-left':
                    wm.style.top = margin + 'px';
                    wm.style.left = margin + 'px';
                    break;
                case 'top-right':
                    wm.style.top = margin + 'px';
                    wm.style.right = margin + 'px';
                    break;
                case 'bottom-left':
                    wm.style.bottom = margin + 'px';
                    wm.style.left = margin + 'px';
                    break;
                case 'bottom-right':
                default:
                    wm.style.bottom = margin + 'px';
                    wm.style.right = margin + 'px';
                    break;
            }
            // Show
            wm.style.opacity = '1';
            // Hide after 2s
            if (watermarkHideTimeoutId) clearTimeout(watermarkHideTimeoutId);
            watermarkHideTimeoutId = setTimeout(() => {
                wm.style.opacity = '0';
            }, 2000);
        };
        // Run immediately then every 5s
        showOnce();
        if (watermarkIntervalId) clearInterval(watermarkIntervalId);
        watermarkIntervalId = setInterval(showOnce, 5000);
    };

    function loadContent(episode) {
        const contentType = episode.dataset.contentType;
        const resourceUrl = episode.dataset.resourceUrl;
        const noteLink = episode.dataset.noteLink;
        
        contentDisplay.innerHTML = ''; // Clear previous content

        if (watermarkIntervalId) {
            clearInterval(watermarkIntervalId);
            watermarkIntervalId = null;
        }
        if (watermarkHideTimeoutId) {
            clearTimeout(watermarkHideTimeoutId);
            watermarkHideTimeoutId = null;
        }

        const prevWatermark = document.getElementById('rs-lms-watermark');
        if (prevWatermark && prevWatermark.parentElement) {
            prevWatermark.parentElement.removeChild(prevWatermark);
        }


        if (contentType === 'video') {
            const contentSrc = episode.dataset.contentSrc;
            const videoTitle = episode.querySelector('h3').innerText;
            // Try to get a description from a data attribute or fallback to a default
            const videoDescription = episode.dataset.description || '';
            // Try to get a detailed notes id from a data attribute
            const notesId = episode.dataset.notesId;
            let notesHtml = '';
            if (notesId) {
                const notesTemplate = document.getElementById(notesId);
                if (notesTemplate) {
                    notesHtml = notesTemplate.innerHTML;
                }
            }
            contentDisplay.innerHTML = `
                <div>
                    <div class="video-container bg-black rounded-lg overflow-hidden shadow-lg">
                        <iframe src="${contentSrc}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    </div>
                    <h2 class="text-2xl font-bold mt-6 mb-2 px-4 md:px-8">${videoTitle}</h2>
                    <div class="text-gray-600 text-base px-4 md:px-8 mb-6" id="video-description">${videoDescription}</div>
                    <div class="video-notes-section px-4 md:px-8 mb-8 prose" id="video-notes">
                        ${notesHtml}
                    </div>
                </div>
            `;
            // Ensure syntax highlighting for code blocks in video notes
            hljs.highlightAll();
        } else if (contentType === 'text') {
            const contentId = episode.dataset.contentId;
            const template = document.getElementById(contentId);
            if (template) {
                contentDisplay.innerHTML = template.innerHTML;
                // After adding the content, tell highlight.js to process it
                hljs.highlightAll();
            } else {
                contentDisplay.innerHTML = `<p class="p-8">Content not found.</p>`;
            }
        }

        // If a markdown note link is provided, fetch via WP REST and render with marked
        if (noteLink) {
            const notesEl = document.getElementById('video-notes');
            if (notesEl) {
                notesEl.innerHTML = `<div class="text-gray-500">Loading notes...</div>`;
                fetch(`${rsLmsRest.apiUrl}notes?url=${encodeURIComponent(noteLink)}`, {
                    method: 'GET',
                    headers: { 'X-WP-Nonce': rsLmsRest.nonce },
                    credentials: 'same-origin'
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.ok && typeof data.markdown === 'string') {
                            const html = (window.marked && window.marked.parse) ? window.marked.parse(data.markdown) : data.markdown;
                            notesEl.innerHTML = html;
                            if (window.hljs) hljs.highlightAll();
                        } else {
                            notesEl.innerHTML = `<div class="text-red-500">Failed to load notes.</div>`;
                        }
                    })
                    .catch(() => {
                        notesEl.innerHTML = `<div class="text-red-500">Failed to load notes.</div>`;
                    });
            }
        }

        // Update download link
        downloadBtn.href = resourceUrl;

        // Update active state in sidebar
        episodes.forEach(ep => ep.classList.remove('active'));
        episode.classList.add('active');

        // Persist last watched per course
        try {
            if (window.rsLmsRest && rsLmsRest.courseId) {
                const courseKey = `${rsLmsRest.storageKeyPrefix}:${rsLmsRest.courseId}`;
                // Store identifiers to find the element again: chapter and ep numbers
                const lastWatched = {
                    chapterId: episode.dataset.chapterId || null,
                    ep: episode.dataset.ep || null
                };
                localStorage.setItem(courseKey, JSON.stringify(lastWatched));
            }
        } catch (e) {
            // ignore storage errors (private mode, quota, etc.)
        }
        
        // Update button states
        updateNavButtons();
        updateCompleteButton(episode);
        updateBookmarkButton(episode);

        // Start watermark loop (best-effort: overlays outside the iframe are not visible when the iframe enters fullscreen)
        const container = contentDisplay.querySelector('.video-container');
        if (container) {
            tryStartWatermarkLoop(container);
        }
    }

    function updateNavButtons() {
        prevBtn.disabled = currentEpisodeIndex === 0;
        nextBtn.disabled = currentEpisodeIndex === allEpisodes.length - 1;
    }

    function updateCompleteButton(episode) {
        if (episode.classList.contains('completed')) {
            completeBtn.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Completed';
            completeBtn.classList.add('bg-green-100', 'text-green-700');
            completeBtn.classList.remove('border-green-500', 'text-green-600', 'hover:bg-green-50');
        } else {
            completeBtn.innerHTML = '<i class="far fa-check-circle mr-2"></i>Mark as Completed';
            completeBtn.classList.remove('bg-green-100', 'text-green-700');
            completeBtn.classList.add('border-green-500', 'text-green-600', 'hover:bg-green-50');
        }
    }
    
    function updateBookmarkButton(episode) {
        const mainBookmarkIcon = bookmarkBtn.querySelector('i');
        const sidebarBookmarkIcon = episode.querySelector('.fa-bookmark');

        if (episode.classList.contains('bookmarked')) {
            mainBookmarkIcon.classList.replace('far', 'fas');
            mainBookmarkIcon.classList.add('text-blue-600');
            sidebarBookmarkIcon.classList.replace('far', 'fas');
            sidebarBookmarkIcon.classList.add('text-blue-600');
        } else {
            mainBookmarkIcon.classList.replace('fas', 'far');
            mainBookmarkIcon.classList.remove('text-blue-600');
            sidebarBookmarkIcon.classList.replace('fas', 'far');
            sidebarBookmarkIcon.classList.remove('text-blue-600');
        }
    }

    function openChapterForEpisode(episode) {
        // Find the parent chapter of the episode
        const parentChapter = episode.closest('.chapter');
        if (!parentChapter) return;
        chapters.forEach((chapter) => {
            if (chapter === parentChapter) {
                chapter.classList.add('open');
                const icon = chapter.querySelector('.chapter-header i');
                if (icon) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            } else {
                chapter.classList.remove('open');
                const icon = chapter.querySelector('.chapter-header i');
                if (icon) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            }
        });
    }

    // Chapter collapse functionality (accordion: only one open at a time, and clicking open chapter collapses it)
    chapters.forEach((chapter) => {
        const header = chapter.querySelector('.chapter-header');
        const icon = header.querySelector('i');
        header.addEventListener('click', () => {
            const isOpen = chapter.classList.contains('open');
            // If already open, collapse it
            if (isOpen) {
                chapter.classList.remove('open');
                if (icon) {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                }
            } else {
                // Close all chapters, open only this one
                chapters.forEach((c) => {
                    c.classList.remove('open');
                    const otherIcon = c.querySelector('.chapter-header i');
                    if (otherIcon) {
                        otherIcon.classList.remove('fa-chevron-down');
                        otherIcon.classList.add('fa-chevron-up');
                    }
                });
                chapter.classList.add('open');
                if (icon) {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                }
            }
        });
    });

    // Episode click functionality
    allEpisodes.forEach((episode, index) => {
        episode.addEventListener('click', (e) => {
            // Prevent toggling bookmark when clicking the main episode body
            if (e.target.closest('.fa-bookmark')) {
                return;
            }
            currentEpisodeIndex = index;
            loadContent(episode);
            closeSidebar(); // Hide sidebar on mobile after click
        });

        // Sidebar bookmark toggle
        const bookmarkIcon = episode.querySelector('.fa-bookmark');
        bookmarkIcon.addEventListener('click', async (e) => {
            e.stopPropagation(); // Prevent episode from loading again
            episode.classList.toggle('bookmarked');
            
            if(episode.classList.contains('active')) {
               updateBookmarkButton(episode);
            } else {
               if (episode.classList.contains('bookmarked')) {
                   bookmarkIcon.classList.replace('far', 'fas');
                   bookmarkIcon.classList.add('text-blue-600');
               } else {
                   bookmarkIcon.classList.replace('fas', 'far');
                   bookmarkIcon.classList.remove('text-blue-600');
               }
            }
            try{
                console.log('Updating bookmark status...');
                const chapterId = Number(episode.dataset.chapterId);
                const ep = Number(episode.dataset.ep);
                const bookmarked = episode.classList.contains('bookmarked');
                if (chapterId && ep && window.rsLmsRest) {
                     await fetch(rsLmsRest.apiUrl + 'episode/bookmark', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': rsLmsRest.nonce
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ chapter_id: chapterId, ep, bookmarked })
                    });
                }
            }catch(err) {
                console.error('Error updating bookmark button:', err);
            }
        });
    });

    // Navigation button functionality
    prevBtn.addEventListener('click', () => {
        if (currentEpisodeIndex > 0) {
            currentEpisodeIndex--;
            const episode = allEpisodes[currentEpisodeIndex];
            openChapterForEpisode(episode);
            loadContent(episode);
        }
    });

    nextBtn.addEventListener('click', () => {
        if (currentEpisodeIndex < allEpisodes.length - 1) {
            currentEpisodeIndex++;
            const episode = allEpisodes[currentEpisodeIndex];
            openChapterForEpisode(episode);
            loadContent(episode);
        }
    });

    // Control buttons functionality
    completeBtn.addEventListener('click', async () => {
        const currentEpisode = allEpisodes[currentEpisodeIndex];
        currentEpisode.classList.toggle('completed');
        updateCompleteButton(currentEpisode);
        updateAdminBar();

        try {
            console.log('Updating completion status...');
            const chapterId = Number(currentEpisode.dataset.chapterId);
            const ep = Number(currentEpisode.dataset.ep);
            const completed = currentEpisode.classList.contains('completed');

            if(chapterId && ep && window.rsLmsRest){
                await fetch(rsLmsRest.apiUrl + 'episode/complete', {
                    method: 'POST',
                    headers: {
                        'content-type': 'application/json',
                        'X-WP-Nonce': rsLmsRest.nonce
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({chapter_id: chapterId, ep, completed})
                });
            }
        }catch(err) {
            console.error('Failed to persist completion status', err);
        }
    });

    bookmarkBtn.addEventListener('click', async () => {
        const currentEpisode = allEpisodes[currentEpisodeIndex];
        currentEpisode.classList.toggle('bookmarked');
        updateBookmarkButton(currentEpisode);

        try {
            console.log('Updating bookmark status...');
            const chapterId = Number(currentEpisode.dataset.chapterId);
            const ep = Number(currentEpisode.dataset.ep);
            const bookmarked = currentEpisode.classList.contains('bookmarked');
            if (chapterId && ep && window.rsLmsRest) {
                await fetch(rsLmsRest.apiUrl + 'episode/bookmark', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': rsLmsRest.nonce
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ chapter_id: chapterId, ep, bookmarked })
                });
            }
        } catch (err) {
            console.error('Failed to persist bookmark status', err);
        }
    });

    // Admin bar logic
    function updateAdminBar() {
        // Set username (replace with real logic if needed)
        //const username = 'john.doe';
        //document.getElementById('admin-bar-username').innerHTML = `<i class="fas fa-user-circle mr-1"></i> ${username}`;
        // Calculate percentage complete
        const totalEpisodes = allEpisodes.length;
        const completedEpisodes = allEpisodes.filter(ep => ep.classList.contains('completed')).length;
        const percent = totalEpisodes > 0 ? Math.round((completedEpisodes / totalEpisodes) * 100) : 0;
        document.getElementById('admin-bar-percentage').textContent = percent + '%';
    }
    // Update on load and when marking complete
    updateAdminBar();
    completeBtn.addEventListener('click', () => {
        updateAdminBar();
    });
    // Also update when toggling complete from sidebar (if you add such a feature)

    // Hamburger menu and responsive sidebar
    const hamburgerBtn = document.getElementById('hamburger-btn');
    function isMobile() {
        return window.innerWidth <= 768;
    }
    function closeSidebar() {
        if (isMobile()) {
            sidebar.classList.remove('open');
        }
    }
    hamburgerBtn.addEventListener('click', function() {
        sidebar.classList.add('open');
    });

    function tryRestoreLastWatched(){
        try{
            if(window.rsLmsRest && rsLmsRest.courseId){
                const courseKey = `${rsLmsRest.storageKeyPrefix}:${rsLmsRest.courseId}`;
                const raw = localStorage.getItem(courseKey);
                if(raw){
                    const parsed = JSON.parse(raw);
                    if(parsed && parsed.chapterId && parsed.ep){
                        //Find matching episode element
                        const selector = `.episode[data-chapter-id="${parsed.chapterId}"][data-ep="${parsed.ep}"]`;
                        const el = document.querySelector(selector);
                        if(el){
                            const idx = allEpisodes.indexOf(el);
                            if(idx >= 0){
                                currentEpisodeIndex = idx;
                                loadContent(el);
                                return true;
                            }

                        }
                    }
                }
            }
        }catch(e){
            //ignore storage error
        }
        return false;
    };

    // Hide sidebar by default on mobile
    function setSidebarInitial() {
        if (isMobile()) {
            sidebar.classList.remove('open');
            // Show first episode content on mobile
            if (allEpisodes.length > 0) {
                if(!tryRestoreLastWatched()){
                currentEpisodeIndex = 0;
                loadContent(allEpisodes[0]);
                }
            }
        } else {
            sidebar.classList.add('open');
            // Show first episode content on desktop
            if (allEpisodes.length > 0) {
                if(!tryRestoreLastWatched()){
                currentEpisodeIndex = 0;
                loadContent(allEpisodes[0]);
                }
            }
        }
    }
    setSidebarInitial();
    window.addEventListener('resize', setSidebarInitial);

    // Sidebar resizer functionality
    let isResizing = false;
    let startX = 0;
    let startWidth = 0;

    // Only enable resizing on md and up
    function isResizable() {
        return window.innerWidth >= 768;
    }

    function setResizerVisibility() {
        if (isResizable()) {
            resizer.classList.remove('hidden');
        } else {
            resizer.classList.add('hidden');
            sidebar.style.width = '';
            sidebar.style.flex = '';
        }
    }
    setResizerVisibility();
    window.addEventListener('resize', setResizerVisibility);

    resizer.addEventListener('mousedown', function(e) {
        if (!isResizable()) return;
        isResizing = true;
        startX = e.clientX;
        startWidth = sidebar.offsetWidth;
        document.body.style.cursor = 'col-resize';
        document.body.style.userSelect = 'none';
    });

    document.addEventListener('mousemove', function(e) {
        if (!isResizing || !isResizable()) return;
        let newWidth = startWidth + (e.clientX - startX);
        newWidth = Math.max(180, Math.min(600, newWidth)); // min/max width
        sidebar.style.width = newWidth + 'px';
        sidebar.style.flex = 'none';
    });

    document.addEventListener('mouseup', function() {
        if (isResizing) {
            isResizing = false;
            document.body.style.cursor = '';
            document.body.style.userSelect = '';
        }
    });

    // Sidebar close button functionality
    function setSidebarCloseBtnVisibility() {
        if (window.innerWidth <= 768) {
            sidebarCloseBtn.style.display = 'block';
        } else {
            sidebarCloseBtn.style.display = 'none';
        }
    }
    setSidebarCloseBtnVisibility();
    window.addEventListener('resize', setSidebarCloseBtnVisibility);
    sidebarCloseBtn.addEventListener('click', function() {
        sidebar.classList.remove('open');
    });

    // Light/Dark mode logic
    (function() {
        const root = document.documentElement;
        const themeBtn = document.getElementById('theme-toggle-btn');
        const themeIcon = document.getElementById('theme-toggle-icon');
        // Check localStorage or system preference
        function getPreferredTheme() {
            const stored = localStorage.getItem('theme');
            if (stored) return stored;
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        function setTheme(theme) {
            if (theme === 'dark') {
                root.classList.add('dark');
                themeIcon.className = 'fa fa-sun';
            } else {
                root.classList.remove('dark');
                themeIcon.className = 'fa fa-moon';
            }
            localStorage.setItem('theme', theme);
        }
        // Initial
        setTheme(getPreferredTheme());
        themeBtn.addEventListener('click', function() {
            const isDark = root.classList.contains('dark');
            setTheme(isDark ? 'light' : 'dark');
        });
    })();
});