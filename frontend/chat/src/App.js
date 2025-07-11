
import { HashRouter, Routes, Route } from 'react-router-dom';
import ChatLayout from './pages/ChatLayout'; // 新しいレイアウトコンポーネント
import './styles/globals.css';

const App = () => {
    return (
        <HashRouter>
            <Routes>
                <Route path="/" element={<ChatLayout />} />
                <Route path="/channel/:channelId" element={<ChatLayout />}>
                    <Route path="user/:userId" element={<ChatLayout />} />
                </Route>
            </Routes>
        </HashRouter>
    );
};

export default App;