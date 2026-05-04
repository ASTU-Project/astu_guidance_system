import React from "react";

export default function GuestPage() {
  return (
    <main className="min-h-screen bg-gradient-to-b from-slate-900 to-slate-800 text-slate-100">
      <header className="py-8">
        <div className="mx-auto max-w-5xl px-6 flex items-center justify-between">
          <h1 className="text-2xl font-bold">ASTU Guidance</h1>
          <nav className="space-x-4 text-sm">
            <a className="text-slate-300 hover:text-white" href="#">Home</a>
            <a className="text-slate-300 hover:text-white" href="#">About</a>
            <a className="text-slate-300 hover:text-white" href="#">Contact</a>
          </nav>
        </div>
      </header>

      <section className="mx-auto max-w-4xl px-6 py-20 text-center">
        <h2 className="text-4xl font-extrabold sm:text-5xl">Welcome to ASTU Guidance</h2>
        <p className="mt-4 text-lg text-slate-300">Guidance and resources for students. Browse anonymously as a guest.</p>
        <div className="mt-8 flex justify-center">
          <button className="rounded-full bg-cyan-500 px-6 py-3 text-sm font-semibold text-slate-900 shadow hover:bg-cyan-400">Enter as Guest</button>
        </div>
      </section>

      <section className="border-t border-white/5 bg-white/2 py-12">
        <div className="mx-auto max-w-4xl px-6 grid gap-6 sm:grid-cols-3">
          <div className="rounded-xl bg-white/5 p-6">
            <h3 className="font-semibold">Resources</h3>
            <p className="mt-2 text-sm text-slate-300">Find courses, policies and contacts.</p>
          </div>
          <div className="rounded-xl bg-white/5 p-6">
            <h3 className="font-semibold">Events</h3>
            <p className="mt-2 text-sm text-slate-300">Upcoming guidance events and workshops.</p>
          </div>
          <div className="rounded-xl bg-white/5 p-6">
            <h3 className="font-semibold">Support</h3>
            <p className="mt-2 text-sm text-slate-300">Get help from our support team.</p>
          </div>
        </div>
      </section>

      <footer className="mt-12 border-t border-white/5 py-8">
        <div className="mx-auto max-w-4xl px-6 text-sm text-slate-400">© {new Date().getFullYear()} ASTU Guidance — Guest access</div>
      </footer>
    </main>
  );
}
