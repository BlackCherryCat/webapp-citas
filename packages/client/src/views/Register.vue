<template>
	<div class="register-page">
		<div class="register-container">
			<h1>Registro</h1>

			<form
				@submit.prevent="register"
				name="registerForm"
				class="register-form"
			>
				<label for="name">Nombre:</label>
				<input type="text" id="name" v-model="name" required />

				<label for="email">Email:</label>
				<input type="email" id="email" v-model="email" required />

				<label for="password">Contraseña:</label>
				<input
					type="password"
					id="password"
					v-model="password"
					required
				/>

				<button type="submit">Registrarse</button>
			</form>

			<p v-if="error">{{ error }}</p>

			<router-link to="/login">Iniciar sesión</router-link>
		</div>
	</div>
</template>

<script>
export default {
	data() {
		return {
			loading: false,
			error: null,
			name: "Test",
			email: "test@example.com",
			password: "123"
		}
	},
	methods: {
		async register() {
			this.error = null
			this.loading = true

			const result = await this.$api
				.register({
					name: this.name,
					email: this.email,
					password: this.password
				})
				.catch((error) => {
					console.error(error)

					this.error = error.response.data.error
					return null
				})

			this.loading = false

			if (result) {
				this.$router.push("/login")
			}
		}
	}
}
</script>

<style scoped>
.register-page h1 {
	color: var(--color-text);
}
/* Contenedor full-viewport que centra su contenido */
.register-page {
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
.register-container {
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
.register-form {
	display: flex;
	flex-direction: column;
	gap: 12px;
}

/* Inputs */
.register-form input {
	padding: 10px 12px;
	border-radius: 8px;
	border: 1px solid rgba(0, 0, 0, 0.12);
	background: transparent;
	color: inherit;
	font-size: 1rem;
}

/* Botón */
.register-form button {
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
	.register-container {
		padding: 16px;
		max-width: 100%;
	}
}
</style>
