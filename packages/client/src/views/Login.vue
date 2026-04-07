<template>
	<div class="login-page">
		<div class="login-container">
			<h1>Iniciar sesión</h1>

			<form name="loginForm" @submit.prevent="login" class="login-form">
				<label for="email">Email:</label>
				<input type="email" id="email" v-model="email" required />

				<label for="password">Contraseña:</label>
				<input
					type="password"
					id="password"
					v-model="password"
					required
				/>

				<button type="submit" :disabled="loading">
					Iniciar sesión
				</button>
			</form>

			<div v-if="error" class="error-message">{{ error }}</div>

			<router-link to="/register">Registrarse</router-link>
		</div>
	</div>
</template>

<script>
export default {
	name: "Login",
	data() {
		return {
			loading: false,
			error: null,
			email: "test@example.com",
			password: "123"
		}
	},
	methods: {
		async login() {
			this.error = null
			this.loading = true

			await this.$session.login(
				{
					email: this.email,
					password: this.password
				},
				{
					onSuccess: () => {
						console.log("Login successful")
					},
					onError: (error) => {
						this.error = error.response.data.error
					}
				}
			)

			this.loading = false
		}
	}
}
</script>

<style scoped>
.login-page h1 {
	color: var(--color-text);
}
/* Contenedor full-viewport que centra su contenido */
.login-page {
	display: flex;
	align-items: center; /* centra verticalmente */
	justify-content: center; /* centra horizontalmente */
	min-height: calc(
		100vh - 200px
	); /* deja espacio para header; ajusta si tu header tiene otra altura */
	padding: 24px; /* evita que el contenido pegue en móviles */
	box-sizing: border-box;
}

/* Contenedor del formulario: tamaño contenido, apariencia de tarjeta */
.login-container {
	display: flex;
	flex-direction: column;
	gap: 16px;
	width: 100%;
	max-width: 420px; /* ancho máximo razonable para formularios */
	background: var(--card-bg, #fff);
	color: var(--color-text, #111);
	padding: 20px;
	border-radius: 12px;
	box-shadow: var(--card-shadow, 0 6px 18px rgba(16, 24, 40, 0.06));
}

/* Formulario */
.login-form {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

/* Inputs */
.login-form input {
	padding: 10px 12px;
	border-radius: 8px;
	border: 1px solid rgba(0, 0, 0, 0.12);
	background: transparent;
	color: inherit;
	font-size: 1rem;
}

/* Botón */
.login-form button {
	padding: 10px 14px;
	border-radius: 8px;
	border: none;
	background: var(--color-primary, #2a7b9b);
	color: #fff;
	cursor: pointer;
}

/* Enlaces y mensajes */
a {
	color: var(--color-primary, #2a7b9b);
}

/* Mensajes de error */
.error-message {
	background-color: #f8d7da;
	color: #721c24;
	padding: 0.5rem;
	border-radius: 4px;
	margin-top: 1rem;
}

/* Responsive: en pantallas pequeñas ocupamos casi todo el ancho */
@media (max-width: 480px) {
	.login-container {
		padding: 16px;
		max-width: 100%;
	}
}
</style>
