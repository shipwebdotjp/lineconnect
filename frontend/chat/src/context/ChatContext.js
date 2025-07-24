import { errorId } from '@rjsf/utils';
import React, { createContext, useReducer } from 'react';

// 1. 初期状態を定義
const initialState = {
  // channels: [],
  selectedChannel: null,
  users: [],
  selectedUser: null,
  messages: [],
  isMessageLoading: false,
  isUserLoading: false,
  isUserDataLoading: false,
};

// 2. アクションの種類を定義
export const actionTypes = {
  // SET_CHANNELS: 'SET_CHANNELS',
  // SELECT_CHANNEL: 'SELECT_CHANNEL',
  FETCH_USERS_START: 'FETCH_USERS_START',
  FETCH_USERS_SUCCESS: 'FETCH_USERS_SUCCESS',
  FETCH_USERS_FAILURE: 'FETCH_USERS_FAILURE',
  // SELECT_USER: 'SELECT_USER',
  FETCH_MESSAGES_START: 'FETCH_MESSAGES_START',
  FETCH_MESSAGES_SUCCESS: 'FETCH_MESSAGES_SUCCESS',
  FETCH_OLDER_MESSAGES_SUCCESS: 'FETCH_OLDER_MESSAGES_SUCCESS',
  FETCH_MESSAGES_FAILURE: 'FETCH_MESSAGES_FAILURE',
  FETCH_USER_DATA_START: 'FETCH_USER_DATA_START',
  FETCH_USER_DATA_SUCCESS: 'FETCH_USER_DATA_SUCCESS',
  FETCH_USER_DATA_FAILURE: 'FETCH_USER_DATA_FAILURE',
  RESET_CHAT_STATE: 'RESET_CHAT_STATE',
};

// 3. Reducerを定義
const reducer = (state, action) => {
  switch (action.type) {
    // case actionTypes.SELECT_CHANNEL:
    //   return {
    //     ...state,
    //     selectedChannel: action.payload,
    //     users: [], // チャネルを切り替えたらユーザーリストをクリア
    //     selectedUser: null,
    //     messages: [],
    //   };
    case actionTypes.FETCH_USERS_START:
      return { ...state, isUserLoading: true, error: null };
    case actionTypes.FETCH_USERS_SUCCESS:
      return { ...state, isUserLoading: false, users: action.payload };
    case actionTypes.FETCH_USERS_FAILURE:
      return { ...state, isUserLoading: false, error: action.payload };

    // case actionTypes.SELECT_USER:
    //   return {
    //     ...state,
    //     selectedUser: action.payload,
    //     messages: [],
    //     error: null,
    //   };

    case actionTypes.FETCH_MESSAGES_START:
      return { ...state, isMessageLoading: true, error: null };

    case actionTypes.FETCH_MESSAGES_SUCCESS:
      return { ...state, isMessageLoading: false, messages: action.payload };

    case actionTypes.FETCH_OLDER_MESSAGES_SUCCESS:
      return { ...state, isMessageLoading: false, messages: [...action.payload, ...state.messages] };

    case actionTypes.FETCH_MESSAGES_FAILURE:
      return { ...state, isMessageLoading: false, error: action.payload };

    case actionTypes.FETCH_USER_DATA_START:
      return { ...state, isUserDataLoading: true, error: null };

    case actionTypes.FETCH_USER_DATA_SUCCESS:
      return { ...state, isUserDataLoading: false, selectedUser: action.payload };

    case actionTypes.FETCH_USER_DATA_FAILURE:
      return { ...state, isUserDataLoading: false, error: action.payload };

    case actionTypes.RESET_CHAT_STATE:
      return {
        ...state,
        selectedUser: null,
        messages: [],
        error: null,
      };

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
