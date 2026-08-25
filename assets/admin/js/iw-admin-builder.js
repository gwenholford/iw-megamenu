document.addEventListener('DOMContentLoaded', () => {
	const root = document.getElementById('iw-mega-menu-builder-root');
	if (!root) return;

	class IWMegaMenuBuilder {
		constructor() {
			this.menus = [];
			this.currentMenuId = null;
			this.currentMenuData = { items: [], settings: {} };
			this.templates = [];
			this.isMobilePreview = false;

			this.init();
		}

		async init() {
			this.renderLoading();
			await Promise.all([
				this.fetchMenus(),
				this.fetchTemplates()
			]);
			this.render();
		}

		async fetchMenus() {
			try {
				this.menus = await wp.apiFetch({ path: '/iw-mega-menu/v1/menus' });
			} catch (error) {
				console.error('Error fetching menus:', error);
			}
		}

		async fetchTemplates() {
			try {
				this.templates = await wp.apiFetch({ path: '/iw-mega-menu/v1/templates' });
			} catch (error) {
				console.error('Error fetching templates:', error);
			}
		}

		async loadMenu(id) {
			this.renderLoading();
			try {
				const response = await wp.apiFetch({ path: `/iw-mega-menu/v1/menus/${id}` });
				this.currentMenuId = response.id;
				this.currentMenuData = response.data || { items: [], settings: {} };
				this.render();
			} catch (error) {
				console.error('Error loading menu:', error);
			}
		}

		renderLoading() {
			root.innerHTML = '<p>Loading builder...</p>';
		}

		render() {
			root.innerHTML = `
				<div class="iw-builder-header">
					<div class="iw-builder-controls">
						<select id="iw-menu-select">
							<option value="">-- Select a Menu --</option>
							${this.menus.map(m => `<option value="${m.id}" ${this.currentMenuId == m.id ? 'selected' : ''}>${m.title}</option>`).join('')}
						</select>
						<button class="button" id="iw-btn-new">New Menu</button>
					</div>
					<div class="iw-builder-controls">
						<button class="button" id="iw-btn-desktop">Desktop</button>
						<button class="button" id="iw-btn-mobile">Mobile</button>
						<button class="button button-primary" id="iw-btn-save" ${!this.currentMenuId ? 'disabled' : ''}>Save Menu</button>
					</div>
				</div>
				<div class="iw-builder-main">
					<div class="iw-builder-tree-panel">
						${this.currentMenuId ? this.renderTree() : '<p>Select or create a menu to begin editing.</p>'}
					</div>
					<div class="iw-builder-preview-panel ${this.isMobilePreview ? 'is-mobile' : ''}">
						${this.currentMenuId ? '<div style="padding:20px;">Live Preview Placeholder (Requires Frontend Styles/DOM rendering logic)</div>' : ''}
					</div>
				</div>
			`;

			this.attachEvents();
		}

		renderTree() {
			return `
				<button class="button" id="iw-btn-add-top">Add Top-Level Item</button>
				<div id="iw-tree-container">
					<!-- Tree rendering logic will go here -->
					<p style="margin-top:10px;"><i>Tree Editor UI initialized.</i></p>
				</div>
			`;
		}

		async createMenu() {
			const title = prompt('Enter a name for the new Mega Menu:');
			if (!title) return;

			this.renderLoading();
			try {
				const response = await wp.apiFetch({
					path: '/iw-mega-menu/v1/menus',
					method: 'POST',
					data: { title: title, data: { items: [], settings: {} } }
				});
				await this.fetchMenus();
				this.loadMenu(response.id);
			} catch (error) {
				console.error('Error creating menu:', error);
				alert('Failed to create menu. Check console for details.');
				this.render();
			}
		}

		async saveMenu() {
			if (!this.currentMenuId) return;
			
			const btnSave = document.getElementById('iw-btn-save');
			if (btnSave) {
				btnSave.textContent = 'Saving...';
				btnSave.disabled = true;
			}
			
			try {
				await wp.apiFetch({
					path: `/iw-mega-menu/v1/menus/${this.currentMenuId}`,
					method: 'POST',
					data: { data: this.currentMenuData }
				});
				if (btnSave) {
					btnSave.textContent = 'Saved!';
					setTimeout(() => {
						btnSave.textContent = 'Save Menu';
						btnSave.disabled = false;
					}, 2000);
				}
			} catch (error) {
				console.error('Error saving menu:', error);
				alert('Failed to save menu. Check console for details.');
				if (btnSave) {
					btnSave.textContent = 'Save Menu';
					btnSave.disabled = false;
				}
			}
		}

		attachEvents() {
			const select = document.getElementById('iw-menu-select');
			if (select) {
				select.addEventListener('change', (e) => {
					if (e.target.value) {
						this.loadMenu(e.target.value);
					} else {
						this.currentMenuId = null;
						this.currentMenuData = { items: [], settings: {} };
						this.render();
					}
				});
			}

			const btnNew = document.getElementById('iw-btn-new');
			if (btnNew) {
				btnNew.addEventListener('click', () => this.createMenu());
			}

			const btnSave = document.getElementById('iw-btn-save');
			if (btnSave) {
				btnSave.addEventListener('click', () => this.saveMenu());
			}

			const btnDesktop = document.getElementById('iw-btn-desktop');
			if (btnDesktop) {
				btnDesktop.addEventListener('click', () => {
					this.isMobilePreview = false;
					this.render();
				});
			}

			const btnMobile = document.getElementById('iw-btn-mobile');
			if (btnMobile) {
				btnMobile.addEventListener('click', () => {
					this.isMobilePreview = true;
					this.render();
				});
			}
		}
	}

	new IWMegaMenuBuilder();
});
