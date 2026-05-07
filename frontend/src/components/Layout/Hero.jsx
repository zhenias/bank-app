import reactLogo from '../../assets/react.svg';
import viteLogo from '../../assets/vite.svg';
import heroImg from '../../assets/hero.png';

export const Hero = () => {
    return (
        <div className="relative flex justify-center items-center mb-8">
            <div className="relative">
                <img
                    src={heroImg}
                    className="w-[170px] h-[179px] opacity-90"
                    alt="Hero"
                />
                <img
                    src={reactLogo}
                    className="absolute top-1/2 left-1/2 transform -translate-x-1/3 -translate-y-1/2 w-16 h-16 animate-spin-slow"
                    alt="React logo"
                />
                <img
                    src={viteLogo}
                    className="absolute top-1/2 left-1/2 transform translate-x-1/4 -translate-y-1/2 w-12 h-12"
                    alt="Vite logo"
                />
            </div>
        </div>
    );
};