import React, { createContext, useReducer } from 'react';

// 1. 初期状態を定義
const initialState = {
  channels: [],
  selectedChannelId: null,
  users: [],
  selectedUserId: null,
  messages: [],
  isLoading: false,
  isSidebarOpen: false,
};

// 2. アクションの種類を定義
export const actionTypes = {
  SET_CHANNELS: 'SET_CHANNELS',
  SELECT_CHANNEL: 'SELECT_CHANNEL',
  FETCH_USERS_START: 'FETCH_USERS_START',
  FETCH_USERS_SUCCESS: 'FETCH_USERS_SUCCESS',
  FETCH_USERS_FAILURE: 'FETCH_USERS_FAILURE',
  SELECT_USER: 'SELECT_USER',
  FETCH_MESSAGES_START: 'FETCH_MESSAGES_START',
  FETCH_MESSAGES_SUCCESS: 'FETCH_MESSAGES_SUCCESS',
  FETCH_MESSAGES_FAILURE: 'FETCH_MESSAGES_FAILURE',
  // ... 他のアクション
};

// 3. Reducerを定義
const reducer = (state, action) => {
  switch (action.type) {
    case actionTypes.SELECT_CHANNEL:
      return {
        ...state,
        selectedChannelId: action.payload,
        users: [], // チャネルを切り替えたらユーザーリストをクリア
        selectedUserId: null,
        messages: [],
      };
    case actionTypes.FETCH_USERS_START:
      return { ...state, isLoading: true, error: null };
    case actionTypes.FETCH_USERS_SUCCESS:
      return { ...state, isLoading: false, users: action.payload };
    case actionTypes.FETCH_USERS_FAILURE:
      return { ...state, isLoading: false, error: action.payload };

    case actionTypes.SELECT_USER:
      return {
        ...state,
        selectedUserId: action.payload,
        messages: [],
        error: null,
      };

    case actionTypes.FETCH_MESSAGES_START:
      return { ...state, isLoading: true, error: null };

    case actionTypes.FETCH_MESSAGES_SUCCESS:
      return { ...state, isLoading: false, messages: action.payload };

    case actionTypes.FETCH_MESSAGES_FAILURE:
      return { ...state, isLoading: false, error: action.payload };

    default:
      return state;
  }
};

// 4. Contextを作成
export const ChatContext = createContext();

// 5. Providerコンポーネントを作成
export const ChatProvider = ({ children }) => {
  const [state, dispatch] = useReducer(reducer, initialState);

  return (
    <ChatContext.Provider value={{ state, dispatch }}>
      {children}
    </ChatContext.Provider>
  );
};
