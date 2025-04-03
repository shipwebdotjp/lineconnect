import { Outlet, Link, NavLink, useLoaderData, Form, redirect, useNavigation, ScrollRestoration, } from "react-router-dom";
import { useState } from "react";

const __ = wp.i18n.__;
export default function Root() {
    const [isMenuOpen, setIsMenuOpen] = useState(false);

    return (
        <div className="w-full">
            <header className="bg-gray-600 mx-auto text-white">
                <div className="flex justify-between items-stretch">
                    <div className={`md:block ${isMenuOpen ? "block" : "hidden"}`}>
                        <ul className="pb-1 md:p-0 md:flex md:justify-center">
                            <li className="block">
                                <NavLink to="channels" className={({ isActive }) =>
                                    `block w-full text-base text-white px-2 md:px-4 py-4 my-4 md:my-0 hover:text-gray-800 hover:bg-gray-400 focus:outline-none ${isActive ? 'bg-gray-700' : ''}`}>
                                    {__('Channels', 'lineconnect')}
                                </NavLink>
                            </li>
                        </ul>
                    </div>
                    <div className="flex justify-between items-stretch">
                        <button
                            className="px-2 py-4 md:hidden"
                            onClick={() => setIsMenuOpen(!isMenuOpen)}
                        >
                            <svg className="h-6 w-6 fill-current" viewBox="0 0 24 24">
                                <path v-show="!isMenuOpen" d="M24 6h-24v-4h24v4zm0 4h-24v4h24v-4zm0 8h-24v4h24v-4z" />
                                <path v-show="isMenuOpen" d="M24 20.188l-8.315-8.209 8.2-8.282-3.697-3.697-8.212 8.318-8.31-8.203-3.666 3.666 8.321 8.24-8.206 8.313 3.666 3.666 8.237-8.318 8.285 8.203z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </header>
            <ScrollRestoration />
            <Outlet />
        </div>
    );
}