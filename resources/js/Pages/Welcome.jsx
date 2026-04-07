export default function Welcome() {
    return (
        <div className="welcome-wrap min-h-screen flex items-center justify-center">
            <section className="welcome-card">
                <h1 className="welcome-title mb-4 text-3xl font-bold">
                    Inertia + React <span className="welcome-accent">Now Using Plain CSS</span>
                </h1>
                <p className="welcome-text">
                    Utility-framework styling has been removed from this project. This page is now styled using regular CSS from
                    resources/css/app.css.
                </p>
                <span className="welcome-pill">Styles Ready</span>
            </section>
        </div>
    );
}
