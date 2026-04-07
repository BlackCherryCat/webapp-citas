<template>
	<div class="app-loading" v-if="loading">
		<p>Loading...</p>
	</div>

	<div id="app" v-else="!loading">
		<header>
			<nav class="header-account">
				<router-link class="btn" to="/">Inicio</router-link>
				<router-link class="btn" to="/calendar">Calendario</router-link>
			</nav>

			<!-- Right side actions: siempre visible, alineadas a la derecha -->
			<div class="header-actions">
				<!-- Comprar Premium (solo si hay user y no es premium) -->
				<button
					v-if="user && user.is_premium == 0"
					class="btn buy-premium"
					@click="buyPremium"
				>
					Comprar Premium
				</button>

				<!-- Toggle tema: siempre visible -->
				<button
					class="btn theme-toggle"
					@click="toggleTheme"
					:aria-pressed="darkMode.toString()"
					:title="
						darkMode
							? 'Cambiar a modo claro'
							: 'Cambiar a modo oscuro'
					"
				>
					<span
						v-if="darkMode"
						class="icon"
						aria-hidden="true"
						v-html="sunSvg"
					/>
					<span
						v-else
						class="icon"
						aria-hidden="true"
						v-html="moonSvg"
					/>
					<span class="sr-only">
						{{ darkMode ? "Modo claro" : "Modo oscuro" }}
					</span>
				</button>

				<!-- Account area -->
				<div class="header-account">
					<div class="header-account-content" v-if="user">
						<div class="header-account-avatar">
							<img :src="user.avatar" alt="Avatar" />
						</div>
						<p class="account-info">
							{{ user.name }}
							<span class="muted">({{ user.email }})</span>
						</p>
						<button class="btn" @click="editUser">
							Editar usuario
						</button>
						<Modal :isOpen="modalOpen" @close="handleModalClose" />
						<button class="btn secondary" @click="toLogout">
							Logout
						</button>
					</div>

					<div class="header-account-content" v-else>
						<button class="btn" @click="toLogin">
							Iniciar sesión
						</button>
					</div>

					<div
						v-if="user && user.is_premium == 1"
						class="header-account-bg-wrapper"
					/>
				</div>
			</div>
		</header>

		<main>
			<router-view />
		</main>
	</div>
</template>

<script>
import Modal from "./components/ModalConfig.vue"
import sunSvg from "@/assets/sun.svg?raw"
import moonSvg from "@/assets/moon.svg?raw"

export default {
	components: { Modal },
	data() {
		return {
			loading: true,
			user: null,
			modalOpen: false,
			darkMode: false,
			// SVGs para iconos
			sunSvg,
			moonSvg
		}
	},
	methods: {
		toLogin() {
			this.$router.push("/login")
		},
		toLogout() {
			this.$router.push("/logout")
		},
		editUser() {
			this.modalOpen = true
		},
		handleModalClose() {
			this.modalOpen = false
		},
		async buyPremium() {
			const response = await this.$api.buyPremium()
			window.location.href = response.url
		},
		toggleTheme() {
			this.darkMode = !this.darkMode
			try {
				localStorage.setItem("darkMode", this.darkMode ? "1" : "0")
			} catch (e) {
				// ignore storage errors
			}
			this.applyTheme()
		},
		applyTheme() {
			document.documentElement.classList.toggle("dark", this.darkMode)
		}
	},
	async mounted() {
		// Inicializar tema (siempre, antes de renderizar interacción)
		try {
			const stored = localStorage.getItem("darkMode")
			if (stored !== null) {
				this.darkMode = stored === "1"
			} else if (
				window.matchMedia &&
				window.matchMedia("(prefers-color-scheme: dark)").matches
			) {
				this.darkMode = true
			} else {
				this.darkMode = false
			}
		} catch (e) {
			this.darkMode = false
		}
		this.applyTheme()

		// Cargar usuario si hay token
		const isAuthed = this.$session && this.$session.token !== undefined

		if (!isAuthed) {
			this.loading = false
			return
		}

		this.user = await this.$api.selfUser().catch(() => null)
		window.user = this.user

		this.appointments = await this.$api.selfAppointments().catch(() => null)
		window.appointments = this.appointments

		this.loading = false
	}
}
</script>

<style scoped>
/* Layout header */
header {
	display: flex;
	align-items: center;
	width: 100%;
	gap: 20px;
	padding: 20px;
	background-color: var(--color-background);
	border-bottom: 2px solid var(--color-text);
	margin-bottom: 20px;
}
a {
	text-decoration: none;
	color: var(--color-text);
}

/* Nav izquierdo */
header nav {
	display: flex;
	gap: 10px;
}

/* Acciones a la derecha: ocupa el espacio restante y alinea items */
.header-actions {
	margin-left: auto;
	display: flex;
	align-items: center;
	gap: 10px;
}

/* Botones base */
.btn {
	background: transparent;
	border: 2px solid rgba(0, 0, 0, 0.08);
	padding: 6px 10px;
	border-radius: 10px;
	cursor: pointer;
	color: var(--color-text);
	display: inline-flex;
	align-items: center;
	gap: 8px;
	font-size: 0.95rem;
}

/* Variante secundaria (logout) */
.btn.secondary {
	background: rgba(0, 0, 0, 0.04);
}

/* Comprar premium: un poco más destacado */
.btn.buy-premium {
	background: var(--color-primary);
	color: #fff;
	border: none;
}

/* Toggle icono */
.theme-toggle {
	width: 40px;
	height: 36px;
	padding: 6px;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	border-radius: 8px;
	background: transparent;
	border: 1px solid rgba(0, 0, 0, 0.06);
}

/* Icono SVG se adapta al color de texto */
.theme-toggle .icon svg {
	display: block;
	width: 18px;
	height: 18px;
	color: var(--color-text);
}

/* Account area */
.header-account {
	position: relative;
	overflow: hidden;
	background-color: var(--color-background-mute);
	border-radius: 12px;
}

.header-account-content {
	z-index: 15;
	position: relative;
	display: flex;
	align-items: center;
	padding: 10px;
	gap: 10px;
}

.header-account-avatar {
	width: 34px;
	height: 34px;
	overflow: hidden;
	border-radius: 8px;
	flex: 0 0 34px;
}

.header-account-avatar img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.account-info {
	font-weight: 500;
	white-space: nowrap;
	color: var(--color-text);
}

.muted {
	opacity: 0.8;
	font-weight: 400;
	font-size: 0.92rem;
}

/* Background wrapper (premium) - preserved from main.css */
.header-account-bg-wrapper {
	z-index: 10;
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	background: linear-gradient(
		90deg,
		rgba(42, 123, 155, 1) 0%,
		rgba(87, 199, 133, 1) 31%,
		rgba(237, 221, 83, 1) 60%,
		rgba(42, 123, 155, 1) 98%
	);
	background-size: 200% 100%;
	animation: swipeBg 5s both infinite;
	opacity: 0.5;
}

@keyframes swipeBg {
	0% {
		background-position: 0% 0%;
	}
	50% {
		background-position: 100% 0%;
	}
	100% {
		background-position: 0% 0%;
	}
}

/* Small helper for screen-reader only text */
.sr-only {
	position: absolute;
	width: 1px;
	height: 1px;
	padding: 0;
	margin: -1px;
	overflow: hidden;
	clip: rect(0, 0, 0, 0);
	white-space: nowrap;
	border: 0;
}

/* Hover effects */
.btn:hover,
.theme-toggle:hover {
	filter: brightness(0.98);
}

/* Dark-theme adjustments that depend on adding class 'dark' to html */
:root.dark header {
	background-color: var(--color-background);
}
:root.dark .btn {
	border-color: rgba(255, 255, 255, 0.06);
	color: var(--color-text-dark, #e6eef8);
}
:root.dark .btn.buy-premium {
	filter: brightness(0.95);
}
:root.dark .theme-toggle .icon svg {
	color: var(--color-text-dark, #e6eef8);
}
</style>
