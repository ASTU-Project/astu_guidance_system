export default function App() {
  return (
    <main className="min-h-screen bg-slate-950 px-6 py-16 text-slate-100">
      <div className="mx-auto flex max-w-4xl flex-col gap-8">
        <div className="inline-flex w-fit rounded-full border border-cyan-400/30 bg-cyan-400/10 px-4 py-1 text-sm text-cyan-200">
          React + Tailwind is ready
        </div>

        <section className="space-y-4">
          <h1 className="text-4xl font-bold tracking-tight sm:text-6xl">
            ASTU Guidance client starter
          </h1>
          <p className="max-w-2xl text-lg text-slate-300">
            This standalone frontend lives in the <code>client</code> folder and is ready for you to build on.
          </p>
        </section>

        <section className="grid gap-4 sm:grid-cols-3">
          <article className="rounded-2xl border border-white/10 bg-white/5 p-5">
            <h2 className="text-lg font-semibold">React</h2>
            <p className="mt-2 text-sm text-slate-300">Component-based UI with a clean Vite setup.</p>
          </article>
          <article className="rounded-2xl border border-white/10 bg-white/5 p-5">
            <h2 className="text-lg font-semibold">Tailwind</h2>
            <p className="mt-2 text-sm text-slate-300">Utility-first styling is configured and working.</p>
          </article>
          <article className="rounded-2xl border border-white/10 bg-white/5 p-5">
            <h2 className="text-lg font-semibold">Ready</h2>
            <p className="mt-2 text-sm text-slate-300">Run the app from the client folder and start building.</p>
          </article>
        </section>
      </div>
    </main>
  );
}
