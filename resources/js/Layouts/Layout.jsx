export default function Layout({ children }) {
  return (
    <>
    {/* <header className="sticky top-0 z-20 border-b border-slate-200/80 bg-white/95 backdrop-blur-xl">
        <div className="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 sm:px-8">
          <Link href="/" className="text-lg font-bold tracking-tight text-slate-950">
            ASTUMG
          </Link>

          <nav className="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
            <Link href="#about" className="transition hover:text-slate-950">About</Link>
            <Link href="#stats" className="transition hover:text-slate-950">Stats</Link>
            <Link href="#map" className="transition hover:text-slate-950">Map</Link>
            <Link href="#contact" className="transition hover:text-slate-950">Contact</Link>
          </nav>
        </div>
      </header> */}

      <main>
        {children}
      </main>
    </>
    
  );
}
