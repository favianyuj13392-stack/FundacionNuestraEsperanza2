"use client";
import React, { useState } from "react";
import { useAuth } from "@/context/AuthContext";
import { useRouter } from "next/navigation";
import Link from "next/link";
import Image from "next/image";
export default function RegisterPage() {
  const [name, setName] = useState("");
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [passwordConfirmation, setPasswordConfirmation] = useState("");

  const [error, setError] = useState<string | null>(null);
  const [isLoading, setIsLoading] = useState(false);

  const { login } = useAuth();
  const router = useRouter(); // Para redirigir

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setIsLoading(true);
    setError(null);

    if (password !== passwordConfirmation) {
      setError("Las contraseñas no coinciden.");
      setIsLoading(false);
      return;
    }

    // Define la URL de tu API de Laravel
    // Asegúrate de que esta sea la URL correcta en producción
    // Por defecto en desarrollo usar localhost para evitar errores DNS
    const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://127.0.0.1:8000";

    try {
      // Solicitar cookie CSRF antes de operaciones que usan sesión
      await fetch(`${API_URL}/sanctum/csrf-cookie`, {
        method: "GET",
        credentials: "include",
      });

      const response = await fetch(`${API_URL}/api/auth/register`, {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          name,
          email,
          password,
          password_confirmation: passwordConfirmation,
        }),
      });

      const data = await response.json();

      if (!response.ok) {
        // Manejo de errores de Laravel (ej. email ya existe)
        if (data.errors) {
          const firstError = Object.values(data.errors)[0] as string[];
          setError(firstError[0]);
        } else {
          setError(data.message || "Ocurrió un error en el registro.");
        }
        setIsLoading(false);
        return;
      }

      // --- ¡ÉXITO! ---
      // El backend devuelve: { message: "...", data: { token: "...", user: {...} } }
      if (data.data?.user && data.data?.token) {
        login(data.data.user, data.data.token);
        router.push("/perfil");
      } else {
        setError("Respuesta inesperada del servidor.");
      }
    } catch (err) {
      console.error("Error de red o fetch:", err);
      setError("No se pudo conectar al servidor. Inténtalo más tarde.");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    // Recomiendo agregar un Navbar y Footer aquí
    <div className="flex items-center justify-center min-h-screen bg-beige-claro">
      <div className="w-full max-w-md p-8 space-y-6 bg-white rounded-lg shadow-md">
        <div className="flex justify-center mb-4">
          <Link href="/">
            <Image
              src="/IMG/Logo.jpg"
              alt="Volver al Inicio - Fundación Nuestra Esperanza"
              width={180}
              height={54}
              className="cursor-pointer"
            />
          </Link>
        </div>
        <h2 className="text-3xl font-bold text-center text-azul-marino font-title">
          Crear una Cuenta
        </h2>

        <form onSubmit={handleSubmit} className="space-y-4">
          <div>
            <label
              htmlFor="name"
              className="text-sm font-bold text-gray-700 font-sans"
            >
              Nombre Completo
            </label>
            <input
              id="name"
              type="text"
              value={name}
              onChange={(e) => setName(e.target.value)}
              required
              className="w-full px-4 py-2 mt-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-rosa-principal"
            />
          </div>

          <div>
            <label
              htmlFor="email"
              className="text-sm font-bold text-gray-700 font-sans"
            >
              Correo Electrónico
            </label>
            <input
              id="email"
              type="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              required
              className="w-full px-4 py-2 mt-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-rosa-principal"
            />
          </div>

          <div>
            <label
              htmlFor="password"
              className="text-sm font-bold text-gray-700 font-sans"
            >
              Contraseña
            </label>
            <input
              id="password"
              type="password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
              className="w-full px-4 py-2 mt-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-rosa-principal"
            />
          </div>

          <div>
            <label
              htmlFor="passwordConfirmation"
              className="text-sm font-bold text-gray-700 font-sans"
            >
              Confirmar Contraseña
            </label>
            <input
              id="passwordConfirmation"
              type="password"
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
              required
              className="w-full px-4 py-2 mt-2 border rounded-md focus:outline-none focus:ring-2 focus:ring-rosa-principal"
            />
          </div>

          {error && <p className="text-sm text-center text-red-600">{error}</p>}

          <div>
            <button
              type="submit"
              disabled={isLoading}
              className="w-full px-4 py-3 font-bold text-white transition-colors duration-300 rounded-md bg-rosa-principal hover:bg-amarillo-detalle font-button disabled:bg-gray-400"
            >
              {isLoading ? "Creando cuenta..." : "Registrarse"}
            </button>
          </div>
        </form>

        <p className="text-sm text-center text-gray-600 font-sans">
          ¿Ya tienes una cuenta?{" "}
          <Link
            href="/login"
            className="font-bold text-turquesa-secundario hover:underline"
          >
            Inicia Sesión
          </Link>
        </p>
      </div>
    </div>
  );
}
