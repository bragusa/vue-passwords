import { transactionURL } from './_connectioninfo' // Ensure the correct path and file name

const DBAdapter = (setWorking) => {
  //const navigate = useNavigate() // Hook for navigation
  //const route = useRoute()

  const errored = (error, context) => {
    if (error instanceof Error) {
      // If the error is an instance of Error, use its message
      console.error(`Error ${context}:`, error.message)
    } else {
      // Handle other types of errors gracefully
      console.error(`Unknown error in ${context}:`, error)
    }

    if (setWorking) {
      setWorking(false)
    }

    // Navigate to a fallback route
    // navigate('/login')
    // route.fullPath

    // route.push({
    //   name: 'LoginView',
    // })
  }

  const sendPayload = async ({
    transaction,
    method,
    body,
    params = [],
    errorString,
    useWorking = true,
  }) => {
    let waitLayerTimeoutId = null

    try {
      const waitTimeout = 500 // ms

      waitLayerTimeoutId = setTimeout(() => {
        if (setWorking) {
          console.log('should have setworking = true')
          setWorking(true) // Show the wait layer
        }
      }, waitTimeout)

      const urlWithParams = params.reduce((acc, param, index) => {
        const key = Object.keys(param)[0]
        const value = param[key]
        if (value !== undefined) {
          const separator = index === 0 ? '?' : '&'
          return `${acc}${separator}${key}=${encodeURIComponent(value)}`
        }
        return acc
      }, `${transaction}.php`)

      const options = {
        method: method ? method : body ? 'POST' : 'GET',
        credentials: 'include', // Include cookies in the request
      }

      if (body) {
        options.body = body // Add body if it's provided
      }

      console.log('transactionURL = ' + transactionURL)

      const response = await fetch(`${transactionURL}/${urlWithParams}`, options)

      if (!response.ok) {
        throw new Error(`HTTP Error: ${response.status} - ${response.statusText}`)
      }

      const data = await response.json()

      if (waitLayerTimeoutId !== null) {
        clearTimeout(waitLayerTimeoutId)
      }
      if (useWorking && setWorking) {
        setWorking(false)
      }

      return data
    } catch (error) {
      if (waitLayerTimeoutId !== null) {
        clearTimeout(waitLayerTimeoutId)
      }
      if (useWorking && setWorking) {
        setWorking(false)
      }
      errored(error, errorString)
      throw error
    }
  }

  const logout = async () => {
    const transaction = 'logout'
    const data = await sendPayload({
      transaction,
      errorString: 'fetching data',
      useWorking: false,
    })
    return data
  }

  const checkForCookie = async () => {
    const transaction = 'authenticateuser'
    return sendPayload({
      transaction,
      errorString: 'Checking authentication',
      useWorking: false,
    })
  }

  const validateConnection = async () => {
    const transaction = 'checktoken'
    return sendPayload({
      transaction,
      errorString: 'Validating connection',
      useWorking: false,
    })
  }

  const authenticateUser = async (username, password) => {
    const transaction = 'authenticateuser'

    const formData = new FormData()
    formData.append('username', username)
    formData.append('password', password)

    const response = await sendPayload({
      transaction,
      body: formData,
      errorString: 'authenticating user',
    })

    return response
  }

  const userNameTaken = async (username) => {
    const transaction = 'usernametaken'

    const formData = new FormData()
    formData.append('username', username)

    const response = await sendPayload({
      transaction,
      body: formData,
      errorString: 'checking username',
    })

    return response
  }

  const fetchData = async (table, orderBy, useWorking = true) => {
    const transaction = 'fetch'
    const params = [{ tablename: table }, { orderby: orderBy }]

    const data = await sendPayload({
      transaction,
      params,
      errorString: 'fetching data',
      useWorking,
    })

    return data
  }

  const deleteAccount = async (id) => {
    const transaction = 'deleteaccount'

    const formData = new FormData()
    formData.append('id', id)
    formData.append('tablename', 'accounts')

    const response = await sendPayload({
      transaction,
      body: formData,
      errorString: 'deleting account',
    })

    return response
  }

  const updateAccount = async (account) => {
    const transaction = 'updateaccount'

    const formData = new FormData()
    formData.append('id', account.id)
    formData.append('siteurl', account.siteurl || '')
    formData.append('site_aaa', account.siteusername || '')
    formData.append('site_bbb', account.sitepassword || '')
    formData.append('sitename', account.sitename || '')

    const response = await sendPayload({
      transaction,
      body: formData,
      errorString: 'updating account',
    })

    return response
  }

  const insertAccount = async (account) => {
    const transaction = 'insertaccount'

    const formData = new FormData()
    formData.append('id', account.id)
    formData.append('siteurl', account.siteurl || '')
    formData.append('site_aaa', account.siteusername || '')
    formData.append('site_bbb', account.sitepassword || '')
    formData.append('sitename', account.sitename || '')

    const response = await sendPayload({
      transaction,
      body: formData,
      errorString: 'inserting account',
    })

    return response
  }

  const createUserAccount = async (account) => {
    const transaction = 'createuseraccount'

    const formData = new FormData()
    formData.append('username', account.new_1 || '')
    formData.append('password', account.new_2 || '')
    formData.append('email', account.new_3 || '')

    const response = await sendPayload({
      transaction,
      body: formData,
      errorString: 'adding user',
    })

    return response
  }

  const fetchPagedData = async (
    table,
    orderBy,
    pageSize,
    offset,
    searchString,
    useWorking = true,
  ) => {
    const transaction = 'fetch'
    const params = [
      { tablename: table },
      { orderby: orderBy },
      { offset: offset.toString() },
      { pagesize: pageSize.toString() },
      { searchterm: searchString },
    ]

    const data = await sendPayload({
      transaction,
      params,
      errorString: 'fetching data',
      useWorking,
    })

    return data
  }

  return {
    logout,
    fetchData,
    fetchPagedData,
    checkForCookie,
    authenticateUser,
    updateAccount,
    insertAccount,
    deleteAccount,
    createUserAccount,
    userNameTaken,
    validateConnection,
  }
}

export default DBAdapter
