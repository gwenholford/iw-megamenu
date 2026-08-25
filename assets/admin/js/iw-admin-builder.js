/**
 * IW Mega Menu Admin Builder
 * Full tree editor with live preview, WP menu import, CRUD operations
 *
 * @package IWMegaMenu
 * @version 2.0.0
 */
document.addEventListener('DOMContentLoaded', () => {
	const root = document.getElementById('iw-mega-menu-builder-root');
	if (!root) return;

	// ── Icon color presets (matches Elementor widget) ──
	const ICON_COLORS = {
		'icon-acc-purple':      { label: 'Purple',        hex: '#4D1979' },
		'icon-acc-purple-dark': { label: 'Purple (Dark)', hex: '#2E1A47' },
		'icon-acc-teal':        { label: 'Teal',          hex: '#00B2A9' },
		'icon-acc-blue-dark':   { label: 'Blue (Dark)',   hex: '#002F6C' },
		'icon-acc-blue-light':  { label: 'Blue (Light)',  hex: '#6787B7' },
		'icon-acc-lime':        { label: 'Lime',          hex: '#C1D82F' },
		'icon-acc-yellow':      { label: 'Yellow',        hex: '#F0B323' },
		'icon-acc-orange':      { label: 'Orange',        hex: '#FA4616' },
		'icon-acc-red':         { label: 'Red',           hex: '#AF272F' },
		'icon-acc-black':       { label: 'Black',         hex: '#000000' },
		'icon-acc-grey':        { label: 'Grey',          hex: '#8A8C8F' },
	};

	// ── Utility: unique ID generator ──
	function uid() {
		return 'item_' + Math.random().toString(36).substring(2, 10) + Date.now().toString(36);
	}

	// ── Utility: escape HTML ──
	function esc(str) {
		const d = document.createElement('div');
		d.textContent = str || '';
		return d.innerHTML;
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
			this.currentMenuId = null;
			this.currentMenuTitle = '';
			this.currentMenuData = { items: [], settings: {} };
			this.templates = [];
			this.isMobilePreview = false;
			this.dirty = false;
			this.expandedItems = new Set();
			this.editingItemId = null;

			this.init();
		}

		// ── Data Fetching ──
		async init() {
			this.renderLoading();
			await Promise.all([
				this.fetchMenus(),
				this.fetchTemplates(),
				this.fetchWpMenus(),
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

		async loadMenu(id) {
			this.renderLoading();
			try {
				const res = await wp.apiFetch({ path: `/iw-mega-menu/v1/menus/${id}` });
				this.currentMenuId = res.id;
				this.currentMenuTitle = res.title;
				this.currentMenuData = res.data || { items: [], settings: {} };
				if (!Array.isArray(this.currentMenuData.items)) this.currentMenuData.items = [];
				this.dirty = false;
				this.expandedItems.clear();
				this.editingItemId = null;
				this.render();
			} catch (e) { console.error('Error loading menu:', e); }
		}

		// ── CRUD ──
		async createMenu() {
			const title = prompt('Enter a name for the new Mega Menu:');
			if (!title) return;
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
						this.render();
					} catch (err) { alert('Invalid JSON file.'); }
				};
				reader.readAsText(file);
			};
			input.click();
		}

		// ── Mutations ──
		markDirty() { this.dirty = true; this.updatePreview(); }

		addItem(parentArray, type) {
			const item = createItem(type);
			item.item_label = type === 'top' ? 'New Top Item' : type === 'second' ? 'New Section' : 'New Link';
			parentArray.push(item);
			this.expandedItems.add(item.id);
			this.editingItemId = item.id;
			this.markDirty();
			this.render();
		}

		removeItem(parentArray, index) {
			const item = parentArray[index];
			if (!confirm(`Delete "${item.item_label || 'Untitled'}"?`)) return;
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

		// ── Rendering ──
		renderLoading() { root.innerHTML = '<div class="iw-loading"><span class="spinner is-active"></span> Loading…</div>'; }

		render() {
			const items = this.currentMenuData.items || [];
			root.innerHTML = `
				<div class="iw-builder-header">
					<div class="iw-builder-controls">
						<select id="iw-menu-select">
							<option value="">— Select a Menu —</option>
							${this.menus.map(m => `<option value="${m.id}" ${this.currentMenuId == m.id ? 'selected' : ''}>${esc(m.title)}</option>`).join('')}
						</select>
						<button class="button" id="iw-btn-new" title="Create new menu">+ New</button>
						${this.currentMenuId ? `<button class="button iw-btn-danger" id="iw-btn-delete" title="Delete this menu">Delete</button>` : ''}
					</div>
					<div class="iw-builder-controls">
						${this.currentMenuId ? `
							<button class="button" id="iw-btn-import-wp" title="Import from a WordPress menu">Import WP Menu</button>
							<button class="button" id="iw-btn-export" title="Export as JSON">Export</button>
							<button class="button" id="iw-btn-import-json" title="Import from JSON file">Import JSON</button>
						` : ''}
						<button class="button ${this.isMobilePreview ? '' : 'button-primary'}" id="iw-btn-desktop">Desktop</button>
						<button class="button ${this.isMobilePreview ? 'button-primary' : ''}" id="iw-btn-mobile">Mobile</button>
						<button class="button button-primary" id="iw-btn-save" ${!this.currentMenuId ? 'disabled' : ''}>${this.dirty ? '● Save Menu' : 'Save Menu'}</button>
					</div>
				</div>

				${this.currentMenuId ? `
				<div class="iw-builder-title-bar">
					<label>Menu Name:</label>
					<input type="text" id="iw-menu-title" value="${esc(this.currentMenuTitle)}" />
				</div>
				` : ''}

				<div class="iw-builder-main">
					<div class="iw-builder-tree-panel" id="iw-tree-panel">
						${this.currentMenuId ? this.renderTreePanel(items) : '<div class="iw-empty-state"><p>Select or create a menu to begin editing.</p></div>'}
					</div>
					<div class="iw-builder-preview-panel ${this.isMobilePreview ? 'is-mobile' : ''}" id="iw-preview-panel">
						${this.currentMenuId ? this.renderPreview(items) : '<div class="iw-empty-state"><p>Preview will appear here</p></div>'}
					</div>
				</div>
			`;
			this.attachEvents();
		}

		// ── Tree Panel ──
		renderTreePanel(items) {
			return `
				<div class="iw-tree-toolbar">
					<button class="button button-primary" id="iw-btn-add-top">+ Add Top-Level Item</button>
				</div>
				<div class="iw-tree-list" id="iw-tree-container">
					${items.length === 0 ? '<p class="iw-hint">No items yet. Click "+ Add Top-Level Item" to get started, or import from an existing WordPress menu.</p>' : ''}
					${items.map((item, i) => this.renderTreeItem(item, i, items, 0)).join('')}
				</div>
			`;
		}

		renderTreeItem(item, index, parentArray, depth) {
			const isExpanded = this.expandedItems.has(item.id);
			const isEditing = this.editingItemId === item.id;
			const depthClass = `iw-depth-${depth}`;
			const typeLabels = { top: 'Top', second: '2nd', third: '3rd' };
			const childType = depth === 0 ? 'second' : 'third';
			const canHaveChildren = depth < 2;
			const children = item.children || [];

			return `
			<div class="iw-tree-item ${depthClass} ${isExpanded ? 'is-expanded' : ''}" data-item-id="${item.id}">
				<div class="iw-tree-item-header">
					<div class="iw-tree-item-left">
						<span class="iw-tree-item-type">${typeLabels[item.item_type] || 'Item'}</span>
						<span class="iw-tree-item-label">${esc(item.item_label) || '<em>Untitled</em>'}</span>
						${item.item_badge ? `<span class="iw-tree-badge">${esc(item.item_badge)}</span>` : ''}
						${children.length > 0 ? `<span class="iw-tree-child-count">(${children.length})</span>` : ''}
					</div>
					<div class="iw-tree-item-actions">
						<button class="button iw-btn-sm" data-action="move-up" data-index="${index}" title="Move up">↑</button>
						<button class="button iw-btn-sm" data-action="move-down" data-index="${index}" title="Move down">↓</button>
						<button class="button iw-btn-sm" data-action="toggle" data-item-id="${item.id}" title="${isExpanded ? 'Collapse' : 'Expand'}">${isExpanded ? '▼' : '▶'}</button>
						<button class="button iw-btn-sm" data-action="edit" data-item-id="${item.id}" title="Edit">${isEditing ? '✕' : '✎'}</button>
						<button class="button iw-btn-sm iw-btn-danger" data-action="remove" data-index="${index}" title="Delete">✕</button>
					</div>
				</div>

				${isEditing ? this.renderItemEditor(item) : ''}

				${isExpanded ? `
					<div class="iw-tree-children">
						${children.map((child, ci) => this.renderTreeItem(child, ci, children, depth + 1)).join('')}
						${canHaveChildren ? `<button class="button iw-btn-sm iw-btn-add-child" data-action="add-child" data-parent-id="${item.id}" data-child-type="${childType}">+ Add ${childType === 'second' ? 'Section' : 'Link'}</button>` : ''}
					</div>
				` : ''}
			</div>`;
		}

		renderItemEditor(item) {
			const sidebarOptions = this.templates.map(t => `<option value="${t.id}" ${item.sidebar_template_id == t.id ? 'selected' : ''}>${esc(t.title)}</option>`).join('');
			const iconColorOptions = Object.entries(ICON_COLORS).map(([cls, info]) =>
				`<option value="${cls}" ${item.item_icon_class === cls ? 'selected' : ''}>${info.label}</option>`
			).join('');

			return `
			<div class="iw-tree-item-editor" data-editor-id="${item.id}">
				<div class="iw-editor-row">
					<label>Label</label>
					<input type="text" data-field="item_label" value="${esc(item.item_label)}" placeholder="Menu item label" />
				</div>
				<div class="iw-editor-row">
					<label>URL</label>
					<input type="url" data-field="item_link_url" value="${esc(item.item_link?.url || '')}" placeholder="https://..." />
				</div>
				<div class="iw-editor-row">
					<label>Description</label>
					<input type="text" data-field="item_description" value="${esc(item.item_description || '')}" placeholder="Optional description text" />
				</div>
				<div class="iw-editor-row iw-editor-row--half">
					<div>
						<label>Badge</label>
						<input type="text" data-field="item_badge" value="${esc(item.item_badge || '')}" placeholder="e.g. New!" />
					</div>
					<div>
						<label>Icon Class (FA)</label>
						<input type="text" data-field="item_icon_value" value="${esc(item.item_icon?.value || '')}" placeholder="e.g. fas fa-star" />
					</div>
				</div>
				<div class="iw-editor-row iw-editor-row--half">
					<div>
						<label>Icon Color</label>
						<select data-field="item_icon_class">${iconColorOptions}</select>
					</div>
					${item.item_type === 'second' ? `
					<div>
						<label>Accordion?</label>
						<select data-field="is_accordion">
							<option value="no" ${item.is_accordion !== 'yes' ? 'selected' : ''}>No</option>
							<option value="yes" ${item.is_accordion === 'yes' ? 'selected' : ''}>Yes</option>
						</select>
					</div>` : ''}
					${item.item_type === 'top' ? `
					<div>
						<label>Sidebar Template</label>
						<select data-field="sidebar_template_id">
							<option value="">None</option>
							${sidebarOptions}
						</select>
					</div>` : ''}
				</div>
			</div>`;
		}

		// ── Live Preview ──
		renderPreview(items) {
			if (!items || items.length === 0) {
				return '<div class="iw-empty-state"><p>Add menu items to see a live preview</p></div>';
			}

			const topLinks = items.map((top, i) => {
				const hasDropdown = top.children && top.children.length > 0;
				return `<li class="iw-mega-menu__top-item">
					${hasDropdown
						? `<button class="iw-mega-menu__top-link" type="button" data-preview-top="${i}">
								<span class="iw-mega-menu__link-content">
									${esc(top.item_label)}
									${top.item_badge ? `<span class="iw-mega-menu__badge">${esc(top.item_badge)}</span>` : ''}
								</span>
							</button>`
						: `<a class="iw-mega-menu__top-link" href="${esc(top.item_link?.url || '#')}">
								<span class="iw-mega-menu__link-content">
									${esc(top.item_label)}
									${top.item_badge ? `<span class="iw-mega-menu__badge">${esc(top.item_badge)}</span>` : ''}
								</span>
							</a>`
					}
					${hasDropdown ? this.renderPreviewDropdown(top, i) : ''}
				</li>`;
			}).join('');

			return `
				<div class="iw-preview-container">
					<div class="iw-mega-menu-wrapper" style="background: var(--iw-menu-purple-dark, #2E1A47); border-radius: 8px;">
						<nav class="iw-mega-menu__nav" aria-label="Preview Navigation">
							<ul class="iw-mega-menu__top-level">${topLinks}</ul>
						</nav>
					</div>
				</div>`;
		}

		renderPreviewDropdown(top, topIndex) {
			const children = top.children || [];
			const nonAccordion = children.filter(c => c.is_accordion !== 'yes');
			const accordion = children.filter(c => c.is_accordion === 'yes');
			const hasSidebar = !!top.sidebar_template_id;

			let secondHtml = '';
			if (nonAccordion.length > 0) {
				const cols = [[], []];
				nonAccordion.forEach((s, idx) => { const c = Math.floor(idx / 4); if (c < 2) cols[c].push(s); });
				secondHtml = `<div class="iw-mega-menu__second-list">${cols.map(col =>
					`<div class="iw-mega-menu__second-col">${col.map(s => {
						const hasUrl = s.item_link?.url;
						const iconHtml = s.item_icon?.value ? `<span class="iw-mega-menu__icon ${s.item_icon_class || 'icon-acc-purple'}" aria-hidden="true"><i class="${esc(s.item_icon.value)}"></i></span>` : '';
						return `<${hasUrl ? 'a href="' + esc(s.item_link.url) + '"' : 'div role="heading" aria-level="3"'} class="iw-mega-menu__section-label">
							<span class="iw-mega-menu__link-content">
								${iconHtml}
								${esc(s.item_label)}
								${s.item_badge ? `<span class="iw-mega-menu__badge">${esc(s.item_badge)}</span>` : ''}
							</span>
							${s.item_description ? `<span class="iw-mega-menu__description">${esc(s.item_description)}</span>` : ''}
						</${hasUrl ? 'a' : 'div'}>`;
					}).join('')}</div>`
				).join('')}</div>`;
			}

			let accordionHtml = accordion.map(s => {
				const iconHtml = s.item_icon?.value ? `<span class="iw-mega-menu__icon ${s.item_icon_class || 'icon-acc-purple'}" aria-hidden="true"><i class="${esc(s.item_icon.value)}"></i></span>` : '';
				const thirdLinks = (s.children || []);
				const thirdCols = [[], [], []];
				thirdLinks.forEach((l, idx) => { const c = Math.floor(idx / 2); if (c < 3) thirdCols[c].push(l); });

				return `<div class="iw-mega-menu__section iw-mega-menu__accordion">
					<button class="iw-mega-menu__section-label iw-mega-menu__accordion-toggle" type="button" aria-expanded="false">
						<span class="iw-mega-menu__link-content">
							${iconHtml}
							${esc(s.item_label)}
							${s.item_badge ? `<span class="iw-mega-menu__badge">${esc(s.item_badge)}</span>` : ''}
						</span>
						${s.item_description ? `<span class="iw-mega-menu__description">${esc(s.item_description)}</span>` : ''}
						<span class="iw-mega-menu__caret" aria-hidden="true"><i class="fa-regular fa-square-caret-down"></i></span>
					</button>
					<div class="iw-mega-menu__section-content" hidden>
						<div class="iw-mega-menu__third-list">
							${thirdCols.map(col => `<div class="iw-mega-menu__third-col">${col.map(l =>
								`<a class="iw-mega-menu__third-link" href="${esc(l.item_link?.url || '#')}">
									<span class="iw-mega-menu__link-content">${esc(l.item_label)}${l.item_badge ? `<span class="iw-mega-menu__badge">${esc(l.item_badge)}</span>` : ''}</span>
								</a>`
							).join('')}</div>`).join('')}
						</div>
					</div>
				</div>`;
			}).join('');

			const sidebarClass = hasSidebar ? 'iw-mega-menu__dropdown--with-sidebar' : 'iw-mega-menu__dropdown--no-sidebar';
			return `<div class="iw-mega-menu__dropdown ${sidebarClass}" data-preview-dropdown="${topIndex}" hidden>
				<div class="iw-mega-menu__dropdown-main">
					${secondHtml}
					${accordionHtml}
				</div>
				${hasSidebar ? '<aside class="iw-mega-menu-sidebar"><p>Sidebar Template #' + top.sidebar_template_id + '</p></aside>' : ''}
			</div>`;
		}

		updatePreview() {
			const previewPanel = document.getElementById('iw-preview-panel');
			if (previewPanel && this.currentMenuId) {
				previewPanel.innerHTML = this.renderPreview(this.currentMenuData.items || []);
				this.attachPreviewEvents();
			}
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

		// ── Event Binding ──
		attachEvents() {
			// Menu select
			const select = document.getElementById('iw-menu-select');
			if (select) select.addEventListener('change', (e) => {
				if (this.dirty && !confirm('You have unsaved changes. Discard?')) { e.target.value = this.currentMenuId || ''; return; }
				e.target.value ? this.loadMenu(e.target.value) : (() => { this.currentMenuId = null; this.currentMenuTitle = ''; this.currentMenuData = { items: [], settings: {} }; this.dirty = false; this.render(); })();
			});

			// Buttons
			document.getElementById('iw-btn-new')?.addEventListener('click', () => this.createMenu());
			document.getElementById('iw-btn-delete')?.addEventListener('click', () => this.deleteMenu());
			document.getElementById('iw-btn-save')?.addEventListener('click', () => this.saveMenu());
			document.getElementById('iw-btn-desktop')?.addEventListener('click', () => { this.isMobilePreview = false; this.render(); });
			document.getElementById('iw-btn-mobile')?.addEventListener('click', () => { this.isMobilePreview = true; this.render(); });
			document.getElementById('iw-btn-export')?.addEventListener('click', () => this.exportJson());
			document.getElementById('iw-btn-import-json')?.addEventListener('click', () => this.importJson());

			// Import WP Menu
			document.getElementById('iw-btn-import-wp')?.addEventListener('click', () => {
				if (this.wpMenus.length === 0) { alert('No WordPress menus found.'); return; }
				const choice = prompt(
					'Import from WordPress Menu:\n' +
					this.wpMenus.map((m, i) => `  ${i + 1}. ${m.name} (${m.count} items)`).join('\n') +
					'\n\nEnter the number:'
				);
				const idx = parseInt(choice, 10) - 1;
				if (idx >= 0 && idx < this.wpMenus.length) {
					if (this.currentMenuData.items.length > 0 && !confirm('This will replace existing items. Continue?')) return;
					this.importWpMenu(this.wpMenus[idx].id);
				}
			});

			// Menu title
			document.getElementById('iw-menu-title')?.addEventListener('input', (e) => {
				this.currentMenuTitle = e.target.value;
				this.dirty = true;
			});

			// Add top-level item
			document.getElementById('iw-btn-add-top')?.addEventListener('click', () => {
				this.addItem(this.currentMenuData.items, 'top');
			});

			// Tree item actions (delegated)
			document.getElementById('iw-tree-container')?.addEventListener('click', (e) => {
				const btn = e.target.closest('[data-action]');
				if (!btn) return;
				e.stopPropagation();

				const action = btn.dataset.action;
				const itemId = btn.dataset.itemId;
				const index = parseInt(btn.dataset.index, 10);
				const parentId = btn.dataset.parentId;
				const childType = btn.dataset.childType;

				switch (action) {
					case 'toggle':
						if (this.expandedItems.has(itemId)) this.expandedItems.delete(itemId);
						else this.expandedItems.add(itemId);
						this.render();
						break;
					case 'edit':
						this.editingItemId = this.editingItemId === itemId ? null : itemId;
						if (this.editingItemId) this.expandedItems.add(itemId);
						this.render();
						break;
					case 'remove': {
						const treeItem = btn.closest('.iw-tree-item');
						const treeItemId = treeItem?.dataset.itemId;
						if (treeItemId) {
							const parentArr = this.findParentArray(treeItemId, this.currentMenuData.items);
							if (parentArr) {
								const idx = parentArr.findIndex(it => it.id === treeItemId);
								if (idx !== -1) this.removeItem(parentArr, idx);
							}
						}
						break;
					}
					case 'move-up':
					case 'move-down': {
						const treeItem = btn.closest('.iw-tree-item');
						const treeItemId = treeItem?.dataset.itemId;
						if (treeItemId) {
							const parentArr = this.findParentArray(treeItemId, this.currentMenuData.items);
							if (parentArr) {
								const idx = parentArr.findIndex(it => it.id === treeItemId);
								if (idx !== -1) this.moveItem(parentArr, idx, action === 'move-up' ? -1 : 1);
							}
						}
						break;
					}
					case 'add-child': {
						const parent = this.findItem(parentId, this.currentMenuData.items);
						if (parent) {
							if (!parent.children) parent.children = [];
							this.addItem(parent.children, childType);
						}
						break;
					}
				}
			});

			// Editor field changes (delegated)
			document.getElementById('iw-tree-container')?.addEventListener('input', (e) => {
				const field = e.target.dataset.field;
				if (!field) return;
				const editor = e.target.closest('[data-editor-id]');
				if (!editor) return;
				const itemId = editor.dataset.editorId;
				const item = this.findItem(itemId, this.currentMenuData.items);
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
						item.item_icon.library = e.target.value.startsWith('fas') ? 'fa-solid' : e.target.value.startsWith('far') ? 'fa-regular' : 'fa-solid';
						break;
				}
				this.markDirty();
				// Update the label in the header in real time
				const headerLabel = e.target.closest('.iw-tree-item')?.querySelector('.iw-tree-item-label');
				if (field === 'item_label' && headerLabel) headerLabel.innerHTML = esc(e.target.value) || '<em>Untitled</em>';
			});

			// Editor select changes (delegated)
			document.getElementById('iw-tree-container')?.addEventListener('change', (e) => {
				const field = e.target.dataset.field;
				if (!field) return;
				const editor = e.target.closest('[data-editor-id]');
				if (!editor) return;
				const itemId = editor.dataset.editorId;
				const item = this.findItem(itemId, this.currentMenuData.items);
				if (!item) return;

				switch (field) {
					case 'item_icon_class': item.item_icon_class = e.target.value; break;
					case 'is_accordion': item.is_accordion = e.target.value; break;
					case 'sidebar_template_id': item.sidebar_template_id = e.target.value; break;
				}
				this.markDirty();
			});

			this.attachPreviewEvents();
		}

		attachPreviewEvents() {
			// Preview dropdown toggle
			document.querySelectorAll('[data-preview-top]').forEach(btn => {
				btn.addEventListener('click', () => {
					const idx = btn.dataset.previewTop;
					const dropdown = document.querySelector(`[data-preview-dropdown="${idx}"]`);
					if (!dropdown) return;
					const isOpen = !dropdown.hidden;
					// Close all dropdowns first
					document.querySelectorAll('[data-preview-dropdown]').forEach(d => { d.hidden = true; });
					document.querySelectorAll('[data-preview-top]').forEach(b => b.setAttribute('aria-expanded', 'false'));
					if (!isOpen) {
						dropdown.hidden = false;
						btn.setAttribute('aria-expanded', 'true');
					}
				});
			});

			// Accordion toggles in preview
			document.querySelectorAll('.iw-builder-preview-panel .iw-mega-menu__accordion-toggle').forEach(btn => {
				btn.addEventListener('click', () => {
					const content = btn.nextElementSibling;
					if (content) {
						const isOpen = !content.hidden;
						content.hidden = isOpen;
						btn.setAttribute('aria-expanded', !isOpen);
					}
				});
			});
		}
	}

	new IWMegaMenuBuilder();
});
