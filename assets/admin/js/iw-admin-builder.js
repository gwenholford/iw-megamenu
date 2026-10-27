/**
 * IW Mega Menu Admin Builder
 * Full tree editor with live preview, WP menu import, CRUD operations
 *
 * @package IWMegaMenu
 * @version 3.0.1
 */
document.addEventListener('DOMContentLoaded', () => {
	const root = document.getElementById('iw-mega-menu-builder-root');
	if (!root) return;

	// ── Icon color presets: the ACC brand palette (matches the Elementor widget) ──
	// Retired teal/blue/red classes on older menus still render, mapped to brand colors in CSS.
	const ICON_COLORS = {
		'icon-acc-purple':      { label: 'Purple',      hex: '#4D1979' },
		'icon-acc-purple-dark': { label: 'Dark Purple', hex: '#2E1A47' },
		'icon-acc-orange':      { label: 'Orange',      hex: '#D23F00' },
		'icon-acc-yellow':      { label: 'Yellow',      hex: '#F0B323' },
		'icon-acc-lime':        { label: 'Lime',        hex: '#C1D82F' },
		'icon-acc-black':       { label: 'Black',       hex: '#000000' },
		'icon-acc-grey':        { label: 'Dark Grey',   hex: '#5E5E5E' },
	};

	// ── Curated Font Awesome 6 picker set (the CDN build loaded on this page) ──
	const ICON_LIBRARY = [
		{ group: 'General', icons: [
			['fa-solid fa-house', 'Home'],
			['fa-solid fa-circle-info', 'Info'],
			['fa-solid fa-circle-question', 'Question'],
			['fa-solid fa-star', 'Star'],
			['fa-solid fa-heart', 'Heart'],
			['fa-solid fa-bookmark', 'Bookmark'],
			['fa-solid fa-flag', 'Flag'],
			['fa-solid fa-tag', 'Tag'],
			['fa-solid fa-bell', 'Bell'],
			['fa-solid fa-gear', 'Settings'],
			['fa-solid fa-magnifying-glass', 'Search'],
			['fa-solid fa-list', 'List'],
		]},
		{ group: 'Education', icons: [
			['fa-solid fa-graduation-cap', 'Graduation Cap'],
			['fa-solid fa-book', 'Book'],
			['fa-solid fa-book-open', 'Book Open'],
			['fa-solid fa-school', 'School'],
			['fa-solid fa-chalkboard-user', 'Instructor'],
			['fa-solid fa-pen', 'Pen'],
			['fa-solid fa-pencil', 'Pencil'],
			['fa-solid fa-clipboard', 'Clipboard'],
			['fa-solid fa-file-lines', 'Document'],
			['fa-solid fa-folder', 'Folder'],
			['fa-solid fa-folder-open', 'Folder Open'],
			['fa-solid fa-certificate', 'Certificate'],
		]},
		{ group: 'People & Contact', icons: [
			['fa-solid fa-user', 'User'],
			['fa-solid fa-users', 'Users'],
			['fa-solid fa-user-group', 'Group'],
			['fa-solid fa-envelope', 'Email'],
			['fa-solid fa-phone', 'Phone'],
			['fa-solid fa-comments', 'Comments'],
			['fa-solid fa-comment', 'Comment'],
			['fa-solid fa-paper-plane', 'Send'],
			['fa-solid fa-headset', 'Support'],
			['fa-solid fa-handshake', 'Handshake'],
			['fa-solid fa-calendar', 'Calendar'],
			['fa-solid fa-clock', 'Clock'],
		]},
		{ group: 'Web & Media', icons: [
			['fa-solid fa-globe', 'Globe'],
			['fa-solid fa-link', 'Link'],
			['fa-solid fa-download', 'Download'],
			['fa-solid fa-upload', 'Upload'],
			['fa-solid fa-share-nodes', 'Share'],
			['fa-solid fa-image', 'Image'],
			['fa-solid fa-video', 'Video'],
			['fa-solid fa-camera', 'Camera'],
			['fa-solid fa-play', 'Play'],
			['fa-solid fa-laptop', 'Laptop'],
			['fa-solid fa-desktop', 'Desktop'],
			['fa-solid fa-mobile-screen', 'Mobile'],
		]},
		{ group: 'Campus & Access', icons: [
			['fa-solid fa-building', 'Building'],
			['fa-solid fa-location-dot', 'Location'],
			['fa-solid fa-map', 'Map'],
			['fa-solid fa-car', 'Car'],
			['fa-solid fa-bus', 'Bus'],
			['fa-solid fa-wheelchair', 'Accessibility'],
			['fa-solid fa-universal-access', 'Universal Access'],
			['fa-solid fa-language', 'Language'],
			['fa-solid fa-briefcase', 'Careers'],
			['fa-solid fa-lightbulb', 'Idea'],
			['fa-solid fa-trophy', 'Trophy'],
			['fa-solid fa-chart-line', 'Chart'],
		]},
	];

	// ── Per-level copy: keeps the tree, editor, and hints saying the same thing ──
	const LEVELS = {
		top: {
			chip: 'Top',
			name: 'Top-Level Item',
			childName: 'Section',
			urlHint: 'Leave blank if this item only opens a dropdown.',
			emptyChildren: 'No dropdown sections yet — this item links straight to its URL. Add a section to turn it into a dropdown.',
		},
		second: {
			chip: 'Section',
			name: 'Section',
			childName: 'Link',
			urlHint: 'Optional — makes the section heading itself clickable.',
			emptyChildren: 'No links yet. Links added here appear inside this section on the site.',
		},
		third: {
			chip: 'Link',
			name: 'Link',
			childName: 'Link',
			urlHint: 'Where this link goes.',
			emptyChildren: '',
		},
	};

	// ── Utility: unique ID generator ──
	function uid() {
		return 'item_' + Math.random().toString(36).substring(2, 10) + Date.now().toString(36);
	}

	// ── Utility: escape HTML ──
	// Escapes quotes as well as angle brackets — these values are interpolated into
	// attribute positions (value="…", title="…"), where an unescaped " breaks out.
	function esc(str) {
		return String(str === null || str === undefined ? '' : str)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#39;');
	}

	// ── Utility: derive the Font Awesome library from a class string ──
	function libraryFor(value) {
		if (!value) return '';
		if (value.includes('fa-brands') || value.startsWith('fab')) return 'fa-brands';
		if (value.includes('fa-regular') || value.startsWith('far')) return 'fa-regular';
		return 'fa-solid';
	}

	// ── Utility: create default item ──
	function createItem(type) {
		return {
			id: uid(),
			item_label: '',
			item_link: { url: '' },
			item_description: '',
			item_badge: '',
			item_icon: { value: '', library: '' },
			item_icon_class: 'icon-acc-purple',
			item_type: type || 'top',
			is_accordion: 'no',
			sidebar_template_id: '',
			children: [],
		};
	}

	// ══════════════════════════════════════════════════════════
	// ── Main Builder Class ──
	// ══════════════════════════════════════════════════════════
	class IWMegaMenuBuilder {
		constructor() {
			this.menus = [];
			this.wpMenus = [];
			this.legacyWidgets = [];
			this.isImportingLegacy = false;
			this.currentMenuId = null;
			this.currentMenuTitle = '';
			this.currentMenuData = { items: [], settings: {} };
			this.templates = [];
			this.isMobilePreview = false;
			this.dirty = false;
			this.expandedItems = new Set();
			this.editingItemId = null;
			this.isCreatingMenu = false;
			this.isImportingWp = false;
			this.draggedItemId = null;
			this.iconPickerOpenFor = null;
			this.focusLabelForId = null;
			this.previewOpenTopId = null;
			this.previewOpenSectionId = null;
			this.previewMobileOpen = true;
			this.previewFrame = null;
			this.previewHtml = null; // Last server-rendered preview; null until the first render.
			this.previewTimer = null;
			this.previewRequest = 0;

			window.addEventListener('resize', () => this.fitPreview());

			window.addEventListener('beforeunload', (e) => {
				if (this.dirty) {
					e.preventDefault();
					e.returnValue = '';
				}
			});

			this.init();
		}

		// ── Data Fetching ──
		async init() {
			this.renderLoading();
			await Promise.all([
				this.fetchMenus(),
				this.fetchTemplates(),
				this.fetchWpMenus(),
				this.fetchLegacyWidgets(),
			]);
			this.render();
		}

		async fetchMenus() {
			try { this.menus = await wp.apiFetch({ path: '/iw-mega-menu/v1/menus' }); }
			catch (e) { console.error('Error fetching menus:', e); }
		}

		async fetchTemplates() {
			try { this.templates = await wp.apiFetch({ path: '/iw-mega-menu/v1/templates' }); }
			catch (e) { console.error('Error fetching templates:', e); }
		}

		async fetchWpMenus() {
			try { this.wpMenus = await wp.apiFetch({ path: '/iw-mega-menu/v1/wp-menus' }); }
			catch (e) { console.error('Error fetching WP menus:', e); }
		}

		async fetchLegacyWidgets() {
			try { this.legacyWidgets = await wp.apiFetch({ path: '/iw-mega-menu/v1/legacy-widgets' }); }
			catch (e) { console.error('Error fetching legacy widgets:', e); }
		}

		async importLegacyWidget(postId, widgetId) {
			this.renderLoading();
			try {
				const res = await wp.apiFetch({ path: `/iw-mega-menu/v1/legacy-widgets/${postId}/${widgetId}` });
				this.currentMenuData.items = res.items || [];
				this.dirty = true;
				this.resetEditorState();
				this.render();
			} catch (e) { console.error(e); alert('Import failed.'); this.render(); }
		}

		resetEditorState() {
			this.expandedItems.clear();
			this.editingItemId = null;
			this.iconPickerOpenFor = null;
			this.focusLabelForId = null;
			this.previewOpenTopId = null;
			this.previewOpenSectionId = null;
			this.previewMobileOpen = true;
			this.previewHtml = null; // Don't flash the previous menu while the new one renders.
			this.isCreatingMenu = false;
			this.isImportingWp = false;
			this.isImportingLegacy = false;
		}

		async loadMenu(id) {
			this.renderLoading();
			try {
				const res = await wp.apiFetch({ path: `/iw-mega-menu/v1/menus/${id}` });
				this.currentMenuId = res.id;
				this.currentMenuTitle = res.title;
				this.currentMenuData = res.data || { items: [], settings: {} };
				if (!Array.isArray(this.currentMenuData.items)) this.currentMenuData.items = [];
				this.dirty = false;
				this.resetEditorState();
				this.render();
			} catch (e) { console.error('Error loading menu:', e); }
		}

		// ── CRUD ──
		async confirmCreateMenu() {
			const input = document.getElementById('iw-newmenu-name');
			const title = input ? input.value.trim() : '';
			if (!title) { input?.focus(); return; }
			this.isCreatingMenu = false;
			this.renderLoading();
			try {
				const res = await wp.apiFetch({ path: '/iw-mega-menu/v1/menus', method: 'POST', data: { title, data: { items: [], settings: {} } } });
				await this.fetchMenus();
				await this.loadMenu(res.id);
			} catch (e) { console.error(e); alert('Failed to create menu.'); this.render(); }
		}

		async saveMenu() {
			if (!this.currentMenuId) return;
			const btn = document.getElementById('iw-btn-save');
			if (btn) { btn.textContent = 'Saving…'; btn.disabled = true; }
			try {
				await wp.apiFetch({ path: `/iw-mega-menu/v1/menus/${this.currentMenuId}`, method: 'POST', data: { title: this.currentMenuTitle, data: this.currentMenuData } });
				this.dirty = false;
				if (btn) { btn.textContent = '✓ Saved'; setTimeout(() => { btn.textContent = 'Save Menu'; btn.disabled = false; }, 2000); }
			} catch (e) { console.error(e); alert('Save failed.'); if (btn) { btn.textContent = 'Save Menu'; btn.disabled = false; } }
		}

		async deleteMenu() {
			if (!this.currentMenuId) return;
			if (!confirm(`Delete "${this.currentMenuTitle}"? This cannot be undone.`)) return;
			this.renderLoading();
			try {
				await wp.apiFetch({ path: `/iw-mega-menu/v1/menus/${this.currentMenuId}`, method: 'DELETE' });
				this.currentMenuId = null;
				this.currentMenuTitle = '';
				this.currentMenuData = { items: [], settings: {} };
				this.dirty = false;
				this.resetEditorState();
				await this.fetchMenus();
				this.render();
			} catch (e) { console.error(e); alert('Delete failed.'); this.render(); }
		}

		async importWpMenu(wpMenuId) {
			this.renderLoading();
			try {
				const res = await wp.apiFetch({ path: `/iw-mega-menu/v1/wp-menus/${wpMenuId}` });
				this.currentMenuData.items = res.items || [];
				this.dirty = true;
				// Imported items carry brand-new IDs, so any retained expand/edit state is stale.
				this.resetEditorState();
				this.render();
			} catch (e) { console.error(e); alert('Import failed.'); this.render(); }
		}

		// ── JSON Export / Import ──
		exportJson() {
			const blob = new Blob([JSON.stringify(this.currentMenuData, null, 2)], { type: 'application/json' });
			const a = document.createElement('a');
			a.href = URL.createObjectURL(blob);
			a.download = (this.currentMenuTitle || 'mega-menu') + '.json';
			a.click();
			URL.revokeObjectURL(a.href);
		}

		importJson() {
			const input = document.createElement('input');
			input.type = 'file';
			input.accept = '.json';
			input.onchange = (e) => {
				const file = e.target.files[0];
				if (!file) return;
				const reader = new FileReader();
				reader.onload = (ev) => {
					try {
						const data = JSON.parse(ev.target.result);
						if (data.items) { this.currentMenuData = data; }
						else if (Array.isArray(data)) { this.currentMenuData.items = data; }
						this.dirty = true;
						this.resetEditorState();
						this.render();
					} catch (err) { alert('Invalid JSON file.'); }
				};
				reader.readAsText(file);
			};
			input.click();
		}

		// ── Mutations ──
		markDirty() { this.dirty = true; this.updatePreview(); }

		addItem(parentArray, type, parentItem) {
			const item = createItem(type);
			item.item_label = type === 'top' ? 'New Top Item' : type === 'second' ? 'New Section' : 'New Link';
			parentArray.push(item);
			this.expandedItems.add(item.id);
			if (parentItem) {
				this.expandedItems.add(parentItem.id);
				// Third-level links only render inside accordion sections on desktop, so
				// switch the parent on automatically rather than silently dropping them.
				if (type === 'third' && parentItem.is_accordion !== 'yes') {
					parentItem.is_accordion = 'yes';
				}
			}
			this.editingItemId = item.id;
			this.focusLabelForId = item.id;
			this.iconPickerOpenFor = null;
			this.syncPreviewToItem(item.id);
			this.markDirty();
			this.render();
		}

		duplicateItem(parentArray, index) {
			const clone = this.deepCloneWithNewIds(parentArray[index]);
			clone.item_label = clone.item_label ? clone.item_label + ' (Copy)' : '';
			parentArray.splice(index + 1, 0, clone);
			this.editingItemId = clone.id;
			this.focusLabelForId = clone.id;
			this.iconPickerOpenFor = null;
			this.markDirty();
			this.render();
		}

		deepCloneWithNewIds(item) {
			const clone = JSON.parse(JSON.stringify(item));
			const assign = (node) => {
				node.id = uid();
				if (Array.isArray(node.children)) node.children.forEach(assign);
			};
			assign(clone);
			return clone;
		}

		collectAllIds(items, out) {
			out = out || [];
			items.forEach(item => {
				out.push(item.id);
				if (item.children && item.children.length) this.collectAllIds(item.children, out);
			});
			return out;
		}

		removeItem(parentArray, index) {
			const item = parentArray[index];
			const childCount = (item.children || []).length;
			const extra = childCount ? `\n\nThis also deletes ${childCount} item${childCount === 1 ? '' : 's'} nested inside it.` : '';
			if (!confirm(`Delete "${item.item_label || 'Untitled'}"?${extra}`)) return;
			this.collectAllIds([item]).forEach(id => {
				this.expandedItems.delete(id);
				if (this.editingItemId === id) this.editingItemId = null;
				if (this.iconPickerOpenFor === id) this.iconPickerOpenFor = null;
				if (this.previewOpenSectionId === id) this.previewOpenSectionId = null;
				if (this.previewOpenTopId === id) this.previewOpenTopId = null;
			});
			parentArray.splice(index, 1);
			this.markDirty();
			this.render();
		}

		moveItem(parentArray, index, direction) {
			const newIndex = index + direction;
			if (newIndex < 0 || newIndex >= parentArray.length) return;
			[parentArray[index], parentArray[newIndex]] = [parentArray[newIndex], parentArray[index]];
			this.markDirty();
			this.render();
		}

		reorderWithinArray(array, draggedId, targetId, insertAfter) {
			const fromIndex = array.findIndex(it => it.id === draggedId);
			if (fromIndex === -1 || array.findIndex(it => it.id === targetId) === -1) return;
			const [moved] = array.splice(fromIndex, 1);
			const toIndex = array.findIndex(it => it.id === targetId);
			array.splice(insertAfter ? toIndex + 1 : toIndex, 0, moved);
			this.markDirty();
			this.render();
		}

		toggleEditor(id) {
			this.editingItemId = this.editingItemId === id ? null : id;
			this.iconPickerOpenFor = null;
			if (this.editingItemId) {
				this.expandedItems.add(id);
				this.syncPreviewToItem(id);
			}
			this.render();
		}

		// ── Keep the live preview showing whatever is being edited ──
		syncPreviewToItem(id) {
			const topItem = this.findTopItemFor(id);
			if (topItem) this.previewOpenTopId = topItem.id;

			const item = this.findItem(id, this.currentMenuData.items);
			if (!item) return;
			if (item.item_type === 'second' && item.is_accordion === 'yes') {
				this.previewOpenSectionId = item.id;
			} else if (item.item_type === 'third') {
				const parent = this.findParentItem(id, this.currentMenuData.items, null);
				if (parent) this.previewOpenSectionId = parent.id;
			}
		}

		// Returns the top-level ancestor of any item (or the item itself if top-level).
		findTopItemFor(id) {
			const items = this.currentMenuData.items || [];
			return items.find(top => this.collectAllIds([top]).includes(id)) || null;
		}

		// ── Rendering ──
		renderLoading() { root.innerHTML = '<div class="iw-loading"><span class="spinner is-active"></span> Loading…</div>'; }

		render() {
			const items = this.currentMenuData.items || [];
			root.innerHTML = `
				<div class="iw-builder-header">
					<div class="iw-builder-controls">
						<select id="iw-menu-select" aria-label="Select a menu to edit">
							<option value="">— Select a Menu —</option>
							${this.menus.map(m => `<option value="${m.id}" ${this.currentMenuId == m.id ? 'selected' : ''}>${esc(m.title)}</option>`).join('')}
						</select>
						<button class="button" id="iw-btn-new" title="Create new menu">+ New</button>
						${this.currentMenuId ? `<button class="button iw-btn-danger" id="iw-btn-delete" title="Delete this menu">Delete</button>` : ''}
					</div>
					<div class="iw-builder-controls">
						${this.currentMenuId ? `
							<button class="button" id="iw-btn-import-wp" title="Import from a WordPress menu">Import WP Menu</button>
							${this.legacyWidgets.length ? `<button class="button" id="iw-btn-import-legacy" title="Import items from an existing Elementor IW Mega Menu widget">Import Legacy Widget${this.legacyWidgets.length > 1 ? ` (${this.legacyWidgets.length})` : ''}</button>` : ''}
							<button class="button" id="iw-btn-export" title="Export as JSON">Export</button>
							<button class="button" id="iw-btn-import-json" title="Import from JSON file">Import JSON</button>
						` : ''}
						<button class="button ${this.isMobilePreview ? '' : 'button-primary'}" id="iw-btn-desktop">Desktop</button>
						<button class="button ${this.isMobilePreview ? 'button-primary' : ''}" id="iw-btn-mobile">Mobile</button>
						<button class="button button-primary" id="iw-btn-save" ${!this.currentMenuId ? 'disabled' : ''}>${this.dirty ? '● Save Menu' : 'Save Menu'}</button>
					</div>
				</div>

				${this.currentMenuId ? `
				<p class="description iw-embed-hint">
					Place this menu with the <strong>IW Mega Menu</strong> block, the shortcode <code>[iw_mega_menu id="${esc(this.currentMenuId)}"]</code>,
					the template tag <code>&lt;?php iw_mega_menu( ${esc(this.currentMenuId)} ); ?&gt;</code>, or the Elementor widget.
				</p>` : ''}

				${this.isCreatingMenu ? `
				<div class="iw-builder-inline-bar" id="iw-newmenu-bar">
					<label for="iw-newmenu-name">New Menu Name:</label>
					<input type="text" id="iw-newmenu-name" placeholder="e.g. Main Navigation" />
					<button class="button button-primary iw-btn-sm" id="iw-newmenu-confirm">Create</button>
					<button class="button iw-btn-sm" id="iw-newmenu-cancel">Cancel</button>
				</div>` : ''}

				${this.isImportingWp ? `
				<div class="iw-builder-inline-bar" id="iw-importwp-bar">
					<label for="iw-importwp-select">Import From:</label>
					<select id="iw-importwp-select">
						${this.wpMenus.map(m => `<option value="${m.id}">${esc(m.name)} (${m.count} items)</option>`).join('')}
					</select>
					<button class="button button-primary iw-btn-sm" id="iw-importwp-confirm">Import</button>
					<button class="button iw-btn-sm" id="iw-importwp-cancel">Cancel</button>
				</div>` : ''}

				${this.isImportingLegacy ? `
				<div class="iw-builder-inline-bar" id="iw-importlegacy-bar">
					<label for="iw-importlegacy-select">Import From Widget:</label>
					<select id="iw-importlegacy-select">
						${this.legacyWidgets.map(w => `<option value="${esc(w.post_id)}|${esc(w.widget_id)}">${esc(w.post_title)} (${esc(w.post_type)}) — ${w.item_count} items${w.preview ? ' — ' + esc(w.preview) + '…' : ''}</option>`).join('')}
					</select>
					<button class="button button-primary iw-btn-sm" id="iw-importlegacy-confirm">Import</button>
					<button class="button iw-btn-sm" id="iw-importlegacy-cancel">Cancel</button>
					<p class="iw-field-hint iw-inline-bar-hint">Copies the items from an existing Elementor widget that still uses the Legacy Repeater. The widget itself is not modified — switch it to this saved menu once you're happy with the result.</p>
				</div>` : ''}

				${this.currentMenuId ? `
				<div class="iw-builder-title-bar">
					<label for="iw-menu-title">Menu Name:</label>
					<input type="text" id="iw-menu-title" value="${esc(this.currentMenuTitle)}" />
				</div>
				<p class="iw-hint iw-hint--tips">
					<span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
					<span>Menus nest three levels: <strong>Top</strong> items sit on the nav bar, <strong>Sections</strong> fill their dropdown, and <strong>Links</strong> nest inside a section. Click any row to edit it.</span>
				</p>
				` : ''}

				<div class="iw-builder-main">
					<div class="iw-builder-tree-panel" id="iw-tree-panel">
						${this.currentMenuId ? this.renderTreePanel(items) : '<div class="iw-empty-state"><p>Select or create a menu to begin editing.</p></div>'}
					</div>
					<div class="iw-builder-preview-panel ${this.isMobilePreview ? 'is-mobile' : ''}" id="iw-preview-panel">
						${this.currentMenuId ? `<div class="iw-preview-column">
							<div class="iw-preview-stage" id="iw-preview-stage"></div>
							<p class="iw-preview-caption" id="iw-preview-caption"></p>
						</div>` : '<div class="iw-empty-state"><p>Preview will appear here</p></div>'}
					</div>
				</div>
			`;
			this.attachEvents();
			this.mountPreview();

			if (this.isCreatingMenu) {
				document.getElementById('iw-newmenu-name')?.focus();
			}

			// Only steal focus for a freshly created/duplicated item, never on every re-render.
			if (this.focusLabelForId) {
				const labelInput = document.querySelector(`[data-editor-id="${this.focusLabelForId}"] [data-field="item_label"]`);
				if (labelInput) { labelInput.focus(); labelInput.select(); }
				this.focusLabelForId = null;
			}
		}

		// ── Tree Panel ──
		renderTreePanel(items) {
			return `
				<div class="iw-tree-toolbar">
					<button class="button button-primary" id="iw-btn-add-top">+ Add Top-Level Item</button>
					<button class="button" id="iw-btn-expand-all" title="Expand all items"><span class="dashicons dashicons-editor-expand" aria-hidden="true"></span> Expand All</button>
					<button class="button" id="iw-btn-collapse-all" title="Collapse all items"><span class="dashicons dashicons-editor-contract" aria-hidden="true"></span> Collapse All</button>
				</div>
				<div class="iw-tree-list" id="iw-tree-container">
					${items.length === 0 ? `
						<div class="iw-tree-empty">
							<p><strong>This menu is empty.</strong></p>
							<p>Click <em>+ Add Top-Level Item</em> to create your first nav bar item, or import an existing WordPress menu.</p>
						</div>` : ''}
					${items.map(item => this.renderTreeItem(item, 0)).join('')}
				</div>
			`;
		}

		renderTreeItem(item, depth) {
			const isExpanded = this.expandedItems.has(item.id);
			const isEditing = this.editingItemId === item.id;
			const isEmptyLabel = !item.item_label || !item.item_label.trim();
			const level = LEVELS[item.item_type] || LEVELS.top;
			const childType = depth === 0 ? 'second' : 'third';
			const canHaveChildren = depth < 2;
			const children = item.children || [];
			const iconValue = item.item_icon?.value || '';
			const url = item.item_link?.url || '';
			// A section only renders its links on desktop when Accordion is on.
			const hasHiddenChildren = item.item_type === 'second' && item.is_accordion !== 'yes' && children.length > 0;
			const childNoun = children.length === 1 ? level.childName : level.childName + 's';

			return `
			<div class="iw-tree-item iw-depth-${depth} ${isExpanded ? 'is-expanded' : ''} ${isEditing ? 'is-editing' : ''}" data-item-id="${item.id}">
				<div class="iw-tree-item-header">
					<div class="iw-tree-item-left">
						<span class="iw-drag-handle dashicons dashicons-menu" draggable="true" title="Drag to reorder" aria-hidden="true"></span>
						${canHaveChildren
							? `<button type="button" class="iw-tree-expand" data-action="toggle" data-item-id="${item.id}" aria-expanded="${isExpanded}" aria-label="${isExpanded ? 'Collapse' : 'Expand'} ${esc(item.item_label) || 'item'}">
									<span class="dashicons dashicons-arrow-${isExpanded ? 'down' : 'right'}" aria-hidden="true"></span>
								</button>`
							: '<span class="iw-tree-expand-spacer" aria-hidden="true"></span>'}
						<span class="iw-tree-item-type">${level.chip}</span>
						<span class="iw-tree-item-label ${isEmptyLabel ? 'is-empty' : ''}">${esc(item.item_label) || '<em>Untitled</em>'}</span>
						${isEmptyLabel ? `<span class="dashicons dashicons-warning iw-tree-item-warning" title="This item needs a label"></span>` : ''}
						${item.item_badge ? `<span class="iw-tree-badge">${esc(item.item_badge)}</span>` : ''}
						<span class="iw-tree-meta-group">
							${iconValue ? `<span class="iw-tree-meta ${esc(item.item_icon_class || 'icon-acc-purple')}" title="Icon: ${esc(iconValue)}"><i class="${esc(iconValue)}" aria-hidden="true"></i></span>` : ''}
							${item.item_description ? `<span class="iw-tree-meta dashicons dashicons-editor-alignleft" title="Has a description"></span>` : ''}
							${url ? `<span class="iw-tree-meta dashicons dashicons-admin-links" title="Links to ${esc(url)}"></span>` : ''}
						</span>
						${canHaveChildren && children.length > 0 ? `<span class="iw-tree-child-count">${children.length} ${childNoun.toLowerCase()}</span>` : ''}
						${hasHiddenChildren ? `<span class="iw-tree-warn-pill" title="These links stay hidden on desktop until this section is set to show nested links.">${children.length} hidden</span>` : ''}
						${!canHaveChildren && children.length > 0 ? `<span class="iw-tree-warn-pill" title="A menu supports three levels. These deeper items came from an import and are never displayed — delete this item's nesting or flatten the source menu.">${children.length} too deep</span>` : ''}
					</div>
					<div class="iw-tree-item-actions">
						<button type="button" class="button iw-btn-sm iw-btn-edit ${isEditing ? 'is-active' : ''}" data-action="edit" data-item-id="${item.id}">
							<span class="dashicons dashicons-${isEditing ? 'no-alt' : 'edit'}" aria-hidden="true"></span> ${isEditing ? 'Close' : 'Edit'}
						</button>
						<span class="iw-btn-icon-group">
							<button type="button" class="iw-btn-icon" data-action="move-up" title="Move up" aria-label="Move up"><span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span></button>
							<button type="button" class="iw-btn-icon" data-action="move-down" title="Move down" aria-label="Move down"><span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span></button>
							<button type="button" class="iw-btn-icon" data-action="duplicate" title="Duplicate" aria-label="Duplicate"><span class="dashicons dashicons-admin-page" aria-hidden="true"></span></button>
							<button type="button" class="iw-btn-icon iw-btn-icon--danger" data-action="remove" title="Delete" aria-label="Delete"><span class="dashicons dashicons-trash" aria-hidden="true"></span></button>
						</span>
					</div>
				</div>

				${isEditing ? this.renderItemEditor(item, children) : ''}

				${isExpanded && canHaveChildren ? `
					<div class="iw-tree-children">
						${children.length === 0 ? `<p class="iw-children-empty">${level.emptyChildren}</p>` : ''}
						${children.map(child => this.renderTreeItem(child, depth + 1)).join('')}
						<button type="button" class="button iw-btn-add-child" data-action="add-child" data-parent-id="${item.id}" data-child-type="${childType}">
							<span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span> Add ${level.childName}
						</button>
					</div>
				` : ''}
			</div>`;
		}

		renderItemEditor(item, children) {
			const level = LEVELS[item.item_type] || LEVELS.top;
			const iconValue = item.item_icon?.value || '';
			const iconColorClass = item.item_icon_class || 'icon-acc-purple';
			const pickerOpen = this.iconPickerOpenFor === item.id;
			// Third-level links render as label + badge only, so hiding these avoids
			// implying support the frontend does not have.
			const supportsIconAndDescription = item.item_type !== 'third';
			const childList = children || [];
			const hasHiddenChildren = item.item_type === 'second' && item.is_accordion !== 'yes' && childList.length > 0;

			const iconColorOptions = Object.entries(ICON_COLORS).map(([cls, info]) =>
				`<option value="${cls}" ${iconColorClass === cls ? 'selected' : ''}>${info.label}</option>`
			).join('');
			const sidebarOptions = this.templates.map(t =>
				`<option value="${t.id}" ${item.sidebar_template_id == t.id ? 'selected' : ''}>${t.type ? esc(t.type) + ': ' : ''}${esc(t.title)}</option>`
			).join('');

			return `
			<div class="iw-tree-item-editor" data-editor-id="${item.id}">
				<p class="iw-editor-heading">Editing ${level.name}</p>

				${hasHiddenChildren ? `
				<div class="iw-notice-inline iw-notice-inline--warn">
					<span class="dashicons dashicons-warning" aria-hidden="true"></span>
					<span>This section has <strong>${childList.length}</strong> link${childList.length === 1 ? '' : 's'} that <strong>won't appear on desktop</strong>. Set <em>Show nested links</em> to <strong>Yes</strong> below to display them.</span>
				</div>` : ''}

				<div class="iw-editor-group">
					<p class="iw-editor-group-title">Content</p>
					<div class="iw-editor-row">
						<label>Label <span class="iw-req">required</span></label>
						<input type="text" data-field="item_label" value="${esc(item.item_label)}" placeholder="Text shown in the menu" />
					</div>
					<div class="iw-editor-row">
						<label>Link URL</label>
						<input type="url" data-field="item_link_url" value="${esc(item.item_link?.url || '')}" placeholder="https://…" />
						<p class="iw-field-hint">${level.urlHint}</p>
					</div>
					${supportsIconAndDescription ? `
					<div class="iw-editor-row">
						<label>Description</label>
						<input type="text" data-field="item_description" value="${esc(item.item_description || '')}" placeholder="Optional line of text under the label" />
					</div>` : ''}
					<div class="iw-editor-row">
						<label>Badge</label>
						<input type="text" data-field="item_badge" value="${esc(item.item_badge || '')}" placeholder="e.g. New!" />
						<p class="iw-field-hint">Small pill shown beside the label. Leave blank for none.</p>
					</div>
				</div>

				${supportsIconAndDescription ? `
				<div class="iw-editor-group">
					<p class="iw-editor-group-title">Icon</p>
					<div class="iw-icon-field">
						<div class="iw-icon-current">
							<span class="iw-icon-preview ${esc(iconColorClass)}" data-icon-preview>${iconValue ? `<i class="${esc(iconValue)}" aria-hidden="true"></i>` : '<span class="iw-icon-preview-empty">—</span>'}</span>
							<input type="text" data-field="item_icon_value" value="${esc(iconValue)}" placeholder="No icon — Browse, or type a class" aria-label="Font Awesome icon class" />
							<button type="button" class="button iw-btn-sm" data-action="toggle-icon-picker" aria-expanded="${pickerOpen}">
								<span class="dashicons dashicons-screenoptions" aria-hidden="true"></span> ${pickerOpen ? 'Close' : 'Browse'}
							</button>
							${iconValue ? `<button type="button" class="button iw-btn-sm iw-btn-danger" data-action="clear-icon" title="Remove this icon">Remove</button>` : ''}
						</div>
						${pickerOpen ? `
						<div class="iw-icon-picker">
							<input type="search" class="iw-icon-search" data-icon-search placeholder="Search icons…" aria-label="Search icons" />
							<div class="iw-icon-scroll">
								${ICON_LIBRARY.map(group => `
									<div class="iw-icon-group" data-icon-group>
										<p class="iw-icon-group-title">${esc(group.group)}</p>
										<div class="iw-icon-grid">
											${group.icons.map(([cls, name]) => `
												<button type="button" class="iw-icon-choice ${iconValue === cls ? 'is-selected' : ''}" data-action="pick-icon" data-icon-value="${esc(cls)}" data-icon-name="${esc(name.toLowerCase())}" title="${esc(name)}" aria-label="${esc(name)}">
													<i class="${esc(cls)}" aria-hidden="true"></i>
												</button>`).join('')}
										</div>
									</div>`).join('')}
								<p class="iw-icon-noresults" hidden>No icons match that search. You can also type any Font Awesome class above.</p>
							</div>
						</div>` : ''}
					</div>
					<div class="iw-editor-row">
						<label>Icon Color</label>
						<select data-field="item_icon_class">${iconColorOptions}</select>
						${item.item_type === 'top' ? `<p class="iw-field-hint">Top-level icons always display white so they stay legible on the nav bar.</p>` : ''}
					</div>
				</div>` : ''}

				${item.item_type === 'second' || item.item_type === 'top' ? `
				<div class="iw-editor-group">
					<p class="iw-editor-group-title">Behavior</p>
					${item.item_type === 'second' ? `
					<div class="iw-editor-row">
						<label>Show nested links</label>
						<select data-field="is_accordion">
							<option value="no" ${item.is_accordion !== 'yes' ? 'selected' : ''}>No — this section is a single link</option>
							<option value="yes" ${item.is_accordion === 'yes' ? 'selected' : ''}>Yes — expandable accordion of links</option>
						</select>
						<p class="iw-field-hint">Links nested under this section only appear on desktop when this is set to Yes.</p>
					</div>` : ''}
					${item.item_type === 'top' ? `
					<div class="iw-editor-row">
						<label>Sidebar Template</label>
						<select data-field="sidebar_template_id">
							<option value="">None</option>
							${sidebarOptions}
						</select>
						<p class="iw-field-hint">Shows a pattern (or a saved Elementor template, when Elementor is active) beside this item's dropdown.</p>
					</div>` : ''}
				</div>` : ''}
			</div>`;
		}

		// ── Live Preview ──
		// The preview is the real front-end markup — rendered by the plugin's PHP
		// renderer over REST — inside an isolated frame that loads the same
		// stylesheets as the site, so it can't drift from the live menu and no
		// admin styles can leak into it. Desktop renders at the ACC LG frame
		// (1200px) and scales to fit the panel; Mobile is a 375px phone frame.
		mountPreview() {
			const stage = document.getElementById('iw-preview-stage');
			this.previewFrame = null;
			if (!stage) return;

			const assets = (window.iwMegaMenuAdminData && window.iwMegaMenuAdminData.preview) || {};
			const link = (href) => (href ? `<link rel="stylesheet" href="${esc(href)}">` : '');
			const frame = document.createElement('iframe');
			frame.className = 'iw-preview-frame';
			frame.title = 'Menu preview';
			frame.srcdoc = `<!doctype html><html><head><meta charset="utf-8">
				${link(assets.blockLibrary)}
				<style>${String(assets.globalStyles || '').replace(/<\/style/gi, '<\\/style')}</style>
				${link(assets.fontAwesome)}
				${link(assets.menu)}
				<style>
					html, body { margin: 0; background: #f0f0f1; }
					/* Stand-in for the site header the menu sits in. */
					.iw-preview-bar { box-sizing: border-box; padding: 12px 32px; background: #2E1A47; }
					@media (max-width: 699px) { .iw-preview-bar { padding: 10px 20px; } }
					.iw-preview-empty { margin: 0; color: #fff; font: italic 14px/1.5 sans-serif; }
					/* Re-rendered on every edit, so skip the open/close animations. */
					*, *::before, *::after { transition: none !important; }
				</style>
			</head><body><div class="iw-preview-bar" id="iw-preview-root"></div></body></html>`;

			frame.addEventListener('load', () => {
				this.previewFrame = frame;
				frame.contentDocument.addEventListener('click', (e) => this.handlePreviewClick(e));
				this.writePreview();
			});
			stage.appendChild(frame);
			this.updatePreview();
		}

		// Debounced: typing in a field re-renders once it pauses.
		updatePreview() {
			if (!this.currentMenuId) return;
			clearTimeout(this.previewTimer);
			this.previewTimer = setTimeout(() => this.fetchPreview(), 250);
		}

		async fetchPreview() {
			const request = ++this.previewRequest;
			let html;
			try {
				const res = await wp.apiFetch({ path: '/iw-mega-menu/v1/preview', method: 'POST', data: { data: this.currentMenuData } });
				html = res && typeof res.html === 'string' ? res.html : '';
			} catch (e) {
				console.error('Error rendering preview:', e);
				html = `<p class="iw-preview-empty">Preview unavailable: ${esc(e.message || 'the request failed')}</p>`;
			}
			if (request !== this.previewRequest) return; // A newer edit already superseded this one.
			this.previewHtml = html;
			this.writePreview();
		}

		writePreview() {
			const doc = this.previewFrame && this.previewFrame.contentDocument;
			const root = doc && doc.getElementById('iw-preview-root');
			if (!root || this.previewHtml === null) return;

			root.innerHTML = this.previewHtml || '<p class="iw-preview-empty">Add menu items to see a live preview.</p>';
			this.applyPreviewState(doc);
			this.fitPreview();
		}

		// Open/closed state lives on the builder, so edits never collapse what you were looking at.
		applyPreviewState(doc) {
			const items = this.currentMenuData.items || [];
			const topIndex = items.findIndex(t => t.id === this.previewOpenTopId);
			const top = items[topIndex];
			const sectionIndex = top && Array.isArray(top.children) ? top.children.findIndex(s => s.id === this.previewOpenSectionId) : -1;
			const setOpen = (btn, open) => {
				btn.setAttribute('aria-expanded', String(open));
				const target = doc.getElementById(btn.getAttribute('aria-controls'));
				if (target) target.hidden = !open;
			};

			// Desktop dropdowns and accordions.
			doc.querySelectorAll('.iw-mega-menu__top-link[aria-controls]').forEach(btn => {
				setOpen(btn, Number(btn.dataset.dropdownIndex) === topIndex);
			});
			doc.querySelectorAll('.iw-mega-menu__accordion-toggle').forEach(btn => {
				setOpen(btn, btn.getAttribute('aria-controls') === `iw-mega-menu-preview-dropdown-${topIndex}-section-${sectionIndex}`);
			});

			// Mobile overlay and its sublists.
			const toggle = doc.querySelector('.iw-mega-menu__mobile-toggle');
			if (toggle) setOpen(toggle, this.isMobilePreview && this.previewMobileOpen);
			doc.querySelectorAll('.iw-mega-menu__mobile-link[aria-controls]').forEach(btn => {
				const [i, j] = String(btn.dataset.mobileIndex).split('-').map(Number);
				setOpen(btn, j === undefined ? i === topIndex : i === topIndex && j === sectionIndex);
			});
		}

		handlePreviewClick(e) {
			const target = e.target.closest('a, button');
			if (!target) return;
			e.preventDefault(); // Preview links never navigate away from the builder.

			const items = this.currentMenuData.items || [];
			const toggleTop = (top) => { if (top) this.previewOpenTopId = this.previewOpenTopId === top.id ? null : top.id; };
			const toggleSection = (section) => { if (section) this.previewOpenSectionId = this.previewOpenSectionId === section.id ? null : section.id; };

			if (target.matches('.iw-mega-menu__top-link[aria-controls]')) {
				toggleTop(items[Number(target.dataset.dropdownIndex)]);
			} else if (target.matches('.iw-mega-menu__accordion-toggle')) {
				const match = /-dropdown-(\d+)-section-(\d+)$/.exec(target.getAttribute('aria-controls') || '');
				if (match) toggleSection(items[Number(match[1])]?.children?.[Number(match[2])]);
			} else if (target.matches('.iw-mega-menu__mobile-toggle')) {
				this.previewMobileOpen = true;
			} else if (target.matches('.iw-mega-menu__mobile-close')) {
				this.previewMobileOpen = false;
			} else if (target.matches('.iw-mega-menu__mobile-link[aria-controls]')) {
				const [i, j] = String(target.dataset.mobileIndex).split('-').map(Number);
				if (j === undefined) {
					toggleTop(items[i]);
				} else {
					if (items[i] && this.previewOpenTopId !== items[i].id) this.previewOpenTopId = items[i].id;
					toggleSection(items[i]?.children?.[j]);
				}
			} else {
				return;
			}

			this.applyPreviewState(target.ownerDocument);
			this.fitPreview();
		}

		// Size the frame to its content; scale the desktop frame down to the panel width.
		fitPreview() {
			const stage = document.getElementById('iw-preview-stage');
			const caption = document.getElementById('iw-preview-caption');
			const frame = this.previewFrame;
			const doc = frame && frame.contentDocument;
			if (!stage || !doc || !doc.body) return;

			if (this.isMobilePreview) {
				frame.style.width = '375px';
				frame.style.height = '720px';
				frame.style.transform = '';
				stage.style.height = '';
				if (caption) caption.textContent = 'Mobile · 375px wide · click the menu to explore';
				return;
			}

			const width = 1200;
			const scale = Math.min(1, stage.clientWidth / width);
			if (caption) caption.textContent = `Desktop · ${width}px wide, shown at ${Math.round(scale * 100)}% · click the menu to explore`;
			frame.style.width = `${width}px`;
			frame.style.transform = `scale(${scale})`;
			frame.style.height = '0px'; // Collapse first so scrollHeight measures the content, not the old frame.
			const height = Math.max(doc.documentElement.scrollHeight, doc.body.scrollHeight) + 24;
			frame.style.height = `${height}px`;
			stage.style.height = `${Math.ceil(height * scale)}px`;
		}

		// ── Find item by ID in tree ──
		findItem(id, items) {
			for (const item of items) {
				if (item.id === id) return item;
				if (item.children) {
					const found = this.findItem(id, item.children);
					if (found) return found;
				}
			}
			return null;
		}

		findParentArray(id, items) {
			for (let i = 0; i < items.length; i++) {
				if (items[i].id === id) return items;
				if (items[i].children) {
					const found = this.findParentArray(id, items[i].children);
					if (found) return found;
				}
			}
			return null;
		}

		findParentItem(id, items, parent) {
			for (const it of items) {
				if (it.id === id) return parent || null;
				if (it.children) {
					const found = this.findParentItem(id, it.children, it);
					if (found) return found;
				}
			}
			return null;
		}

		// ── Event Binding ──
		attachEvents() {
			// Menu select
			const select = document.getElementById('iw-menu-select');
			if (select) select.addEventListener('change', (e) => {
				if (this.dirty && !confirm('You have unsaved changes. Discard?')) { e.target.value = this.currentMenuId || ''; return; }
				if (e.target.value) {
					this.loadMenu(e.target.value);
				} else {
					this.currentMenuId = null;
					this.currentMenuTitle = '';
					this.currentMenuData = { items: [], settings: {} };
					this.dirty = false;
					this.resetEditorState();
					this.render();
				}
			});

			// Buttons
			document.getElementById('iw-btn-new')?.addEventListener('click', () => {
				this.isCreatingMenu = true;
				this.isImportingWp = false;
				this.render();
			});
			document.getElementById('iw-btn-delete')?.addEventListener('click', () => this.deleteMenu());
			document.getElementById('iw-btn-save')?.addEventListener('click', () => this.saveMenu());
			document.getElementById('iw-btn-desktop')?.addEventListener('click', () => { this.isMobilePreview = false; this.render(); });
			document.getElementById('iw-btn-mobile')?.addEventListener('click', () => { this.isMobilePreview = true; this.render(); });
			document.getElementById('iw-btn-export')?.addEventListener('click', () => this.exportJson());
			document.getElementById('iw-btn-import-json')?.addEventListener('click', () => this.importJson());

			// Inline "Create Menu" bar
			document.getElementById('iw-newmenu-confirm')?.addEventListener('click', () => this.confirmCreateMenu());
			document.getElementById('iw-newmenu-cancel')?.addEventListener('click', () => { this.isCreatingMenu = false; this.render(); });
			document.getElementById('iw-newmenu-name')?.addEventListener('keydown', (e) => {
				if (e.key === 'Enter') { e.preventDefault(); this.confirmCreateMenu(); }
				if (e.key === 'Escape') { this.isCreatingMenu = false; this.render(); }
			});

			// Inline "Import WP Menu" bar
			document.getElementById('iw-btn-import-wp')?.addEventListener('click', () => {
				if (this.wpMenus.length === 0) { alert('No WordPress menus found.'); return; }
				this.isImportingWp = true;
				this.isCreatingMenu = false;
				this.render();
			});
			document.getElementById('iw-importwp-confirm')?.addEventListener('click', () => {
				const wpSelect = document.getElementById('iw-importwp-select');
				const wpMenuId = wpSelect?.value;
				this.isImportingWp = false;
				if (!wpMenuId) { this.render(); return; }
				if (this.currentMenuData.items.length > 0 && !confirm('This will replace existing items. Continue?')) { this.render(); return; }
				this.importWpMenu(wpMenuId);
			});
			document.getElementById('iw-importwp-cancel')?.addEventListener('click', () => { this.isImportingWp = false; this.render(); });

			// Inline "Import Legacy Widget" bar
			document.getElementById('iw-btn-import-legacy')?.addEventListener('click', () => {
				if (!this.legacyWidgets.length) { alert('No legacy IW Mega Menu widgets found.'); return; }
				this.isImportingLegacy = true;
				this.isCreatingMenu = false;
				this.isImportingWp = false;
				this.render();
			});
			document.getElementById('iw-importlegacy-confirm')?.addEventListener('click', () => {
				const value = document.getElementById('iw-importlegacy-select')?.value || '';
				this.isImportingLegacy = false;
				const sep = value.indexOf('|');
				if (sep === -1) { this.render(); return; }
				if (this.currentMenuData.items.length > 0 && !confirm('This will replace existing items. Continue?')) { this.render(); return; }
				this.importLegacyWidget(value.slice(0, sep), value.slice(sep + 1));
			});
			document.getElementById('iw-importlegacy-cancel')?.addEventListener('click', () => { this.isImportingLegacy = false; this.render(); });

			// Menu title
			document.getElementById('iw-menu-title')?.addEventListener('input', (e) => {
				this.currentMenuTitle = e.target.value;
				this.dirty = true;
			});

			// Add top-level item
			document.getElementById('iw-btn-add-top')?.addEventListener('click', () => {
				this.addItem(this.currentMenuData.items, 'top', null);
			});

			// Expand / Collapse all
			document.getElementById('iw-btn-expand-all')?.addEventListener('click', () => {
				this.collectAllIds(this.currentMenuData.items).forEach(id => this.expandedItems.add(id));
				this.render();
			});
			document.getElementById('iw-btn-collapse-all')?.addEventListener('click', () => {
				this.expandedItems.clear();
				this.editingItemId = null;
				this.iconPickerOpenFor = null;
				this.render();
			});

			const treeContainer = document.getElementById('iw-tree-container');

			// One delegated click handler: named actions first, then row-click-to-edit.
			treeContainer?.addEventListener('click', (e) => {
				const btn = e.target.closest('[data-action]');
				if (btn) {
					this.handleTreeAction(btn);
					return;
				}
				// Clicking anywhere else on a row header opens/closes its editor.
				const header = e.target.closest('.iw-tree-item-header');
				if (header && !e.target.closest('.iw-drag-handle, input, select, textarea, a')) {
					const id = header.closest('.iw-tree-item')?.dataset.itemId;
					if (id) this.toggleEditor(id);
				}
			});

			// Escape closes the icon picker first, then the editor.
			treeContainer?.addEventListener('keydown', (e) => {
				if (e.key !== 'Escape') return;
				if (this.iconPickerOpenFor && e.target.closest('.iw-icon-field')) {
					e.stopPropagation();
					this.iconPickerOpenFor = null;
					this.render();
				} else if (this.editingItemId && e.target.closest('.iw-tree-item-editor')) {
					e.stopPropagation();
					this.editingItemId = null;
					this.render();
				}
			});

			// Editor field changes (delegated)
			treeContainer?.addEventListener('input', (e) => {
				// Icon picker search filters in place — no re-render, no dirty flag.
				if (e.target.hasAttribute('data-icon-search')) {
					this.filterIconPicker(e.target);
					return;
				}

				const field = e.target.dataset.field;
				if (!field) return;
				const editor = e.target.closest('[data-editor-id]');
				if (!editor) return;
				const item = this.findItem(editor.dataset.editorId, this.currentMenuData.items);
				if (!item) return;

				switch (field) {
					case 'item_label': item.item_label = e.target.value; break;
					case 'item_link_url':
						if (!item.item_link) item.item_link = {};
						item.item_link.url = e.target.value;
						break;
					case 'item_description': item.item_description = e.target.value; break;
					case 'item_badge': item.item_badge = e.target.value; break;
					case 'item_icon_value':
						if (!item.item_icon) item.item_icon = {};
						item.item_icon.value = e.target.value;
						item.item_icon.library = libraryFor(e.target.value);
						break;
				}
				this.markDirty();

				if (field === 'item_label') this.syncHeaderLabel(e.target);
				if (field === 'item_icon_value') {
					const preview = editor.querySelector('[data-icon-preview]');
					if (preview) {
						preview.innerHTML = e.target.value
							? `<i class="${esc(e.target.value)}" aria-hidden="true"></i>`
							: '<span class="iw-icon-preview-empty">—</span>';
					}
				}
			});

			// Editor select changes (delegated)
			treeContainer?.addEventListener('change', (e) => {
				const field = e.target.dataset.field;
				if (!field) return;
				const editor = e.target.closest('[data-editor-id]');
				if (!editor) return;
				const item = this.findItem(editor.dataset.editorId, this.currentMenuData.items);
				if (!item) return;

				switch (field) {
					case 'item_icon_class': {
						item.item_icon_class = e.target.value;
						const preview = editor.querySelector('[data-icon-preview]');
						if (preview) preview.className = 'iw-icon-preview ' + e.target.value;
						this.markDirty();
						break;
					}
					case 'is_accordion':
						item.is_accordion = e.target.value;
						if (item.is_accordion === 'yes') this.previewOpenSectionId = item.id;
						this.markDirty();
						// Re-render so the "hidden links" warning stays truthful.
						this.render();
						return;
					case 'sidebar_template_id':
						item.sidebar_template_id = e.target.value;
						this.markDirty();
						break;
				}
			});

			this.attachDragEvents(treeContainer);
		}

		handleTreeAction(btn) {
			const action = btn.dataset.action;
			const itemId = btn.dataset.itemId;
			const treeItemId = btn.closest('.iw-tree-item')?.dataset.itemId;
			const editorId = btn.closest('[data-editor-id]')?.dataset.editorId;

			switch (action) {
				case 'toggle':
					if (this.expandedItems.has(itemId)) this.expandedItems.delete(itemId);
					else this.expandedItems.add(itemId);
					this.render();
					break;

				case 'edit':
					this.toggleEditor(itemId);
					break;

				case 'remove':
				case 'move-up':
				case 'move-down':
				case 'duplicate': {
					if (!treeItemId) break;
					const parentArr = this.findParentArray(treeItemId, this.currentMenuData.items);
					if (!parentArr) break;
					const idx = parentArr.findIndex(it => it.id === treeItemId);
					if (idx === -1) break;
					if (action === 'remove') this.removeItem(parentArr, idx);
					else if (action === 'duplicate') this.duplicateItem(parentArr, idx);
					else this.moveItem(parentArr, idx, action === 'move-up' ? -1 : 1);
					break;
				}

				case 'add-child': {
					const parent = this.findItem(btn.dataset.parentId, this.currentMenuData.items);
					if (parent) {
						if (!parent.children) parent.children = [];
						this.addItem(parent.children, btn.dataset.childType, parent);
					}
					break;
				}

				case 'toggle-icon-picker':
					this.iconPickerOpenFor = this.iconPickerOpenFor === editorId ? null : editorId;
					this.render();
					break;

				case 'clear-icon': {
					const item = this.findItem(editorId, this.currentMenuData.items);
					if (item) {
						item.item_icon = { value: '', library: '' };
						this.markDirty();
						this.render();
					}
					break;
				}

				case 'pick-icon': {
					const item = this.findItem(editorId, this.currentMenuData.items);
					if (item) {
						const value = btn.dataset.iconValue;
						item.item_icon = { value, library: libraryFor(value) };
						this.iconPickerOpenFor = null;
						this.markDirty();
						this.render();
					}
					break;
				}
			}
		}

		// Keep the collapsed row label (and its "needs a label" warning) in sync while typing.
		syncHeaderLabel(input) {
			const headerLeft = input.closest('.iw-tree-item')?.querySelector('.iw-tree-item-header .iw-tree-item-left');
			if (!headerLeft) return;
			const headerLabel = headerLeft.querySelector('.iw-tree-item-label');
			if (!headerLabel) return;
			const isEmpty = !input.value || !input.value.trim();
			headerLabel.innerHTML = esc(input.value) || '<em>Untitled</em>';
			headerLabel.classList.toggle('is-empty', isEmpty);

			let warningIcon = headerLeft.querySelector('.iw-tree-item-warning');
			if (isEmpty && !warningIcon) {
				warningIcon = document.createElement('span');
				warningIcon.className = 'dashicons dashicons-warning iw-tree-item-warning';
				warningIcon.title = 'This item needs a label';
				headerLabel.insertAdjacentElement('afterend', warningIcon);
			} else if (!isEmpty && warningIcon) {
				warningIcon.remove();
			}
		}

		filterIconPicker(searchInput) {
			const picker = searchInput.closest('.iw-icon-picker');
			if (!picker) return;
			const query = searchInput.value.trim().toLowerCase();
			let anyVisible = false;

			picker.querySelectorAll('[data-icon-group]').forEach(group => {
				let groupVisible = false;
				group.querySelectorAll('.iw-icon-choice').forEach(choice => {
					const haystack = (choice.dataset.iconName || '') + ' ' + (choice.dataset.iconValue || '');
					const match = !query || haystack.includes(query);
					choice.hidden = !match;
					if (match) groupVisible = true;
				});
				group.hidden = !groupVisible;
				if (groupVisible) anyVisible = true;
			});

			const noResults = picker.querySelector('.iw-icon-noresults');
			if (noResults) noResults.hidden = anyVisible;
		}

		attachDragEvents(treeContainer) {
			// Drag-and-drop reordering (within the same parent list)
			treeContainer?.addEventListener('dragstart', (e) => {
				const handle = e.target.closest('.iw-drag-handle');
				if (!handle) return;
				const treeItem = handle.closest('.iw-tree-item');
				if (!treeItem) return;
				this.draggedItemId = treeItem.dataset.itemId;
				e.dataTransfer.effectAllowed = 'move';
				e.dataTransfer.setData('text/plain', this.draggedItemId);
				treeItem.classList.add('is-dragging');
			});

			treeContainer?.addEventListener('dragover', (e) => {
				if (!this.draggedItemId) return;
				const header = e.target.closest('.iw-tree-item-header');
				if (!header) return;
				const targetId = header.closest('.iw-tree-item')?.dataset.itemId;
				header.classList.remove('drag-over-top', 'drag-over-bottom', 'drag-forbidden');
				if (!targetId || targetId === this.draggedItemId) return;

				const sourceArr = this.findParentArray(this.draggedItemId, this.currentMenuData.items);
				const targetArr = this.findParentArray(targetId, this.currentMenuData.items);
				if (sourceArr !== targetArr) {
					header.classList.add('drag-forbidden');
					return;
				}
				e.preventDefault();
				e.dataTransfer.dropEffect = 'move';
				const rect = header.getBoundingClientRect();
				const isAfter = (e.clientY - rect.top) > rect.height / 2;
				header.classList.add(isAfter ? 'drag-over-bottom' : 'drag-over-top');
			});

			treeContainer?.addEventListener('dragleave', (e) => {
				e.target.closest('.iw-tree-item-header')?.classList.remove('drag-over-top', 'drag-over-bottom', 'drag-forbidden');
			});

			treeContainer?.addEventListener('drop', (e) => {
				const header = e.target.closest('.iw-tree-item-header');
				if (!header || !this.draggedItemId) return;
				e.preventDefault();
				const isAfter = header.classList.contains('drag-over-bottom');
				header.classList.remove('drag-over-top', 'drag-over-bottom', 'drag-forbidden');
				const targetId = header.closest('.iw-tree-item')?.dataset.itemId;
				const draggedId = this.draggedItemId;
				this.draggedItemId = null;
				if (!targetId || targetId === draggedId) return;

				const sourceArr = this.findParentArray(draggedId, this.currentMenuData.items);
				const targetArr = this.findParentArray(targetId, this.currentMenuData.items);
				if (!sourceArr || sourceArr !== targetArr) return;

				this.reorderWithinArray(sourceArr, draggedId, targetId, isAfter);
			});

			treeContainer?.addEventListener('dragend', () => {
				this.draggedItemId = null;
				document.querySelectorAll('.iw-tree-item.is-dragging').forEach(el => el.classList.remove('is-dragging'));
				document.querySelectorAll('.drag-over-top, .drag-over-bottom, .drag-forbidden').forEach(el => el.classList.remove('drag-over-top', 'drag-over-bottom', 'drag-forbidden'));
			});
		}

	}

	new IWMegaMenuBuilder();
});
