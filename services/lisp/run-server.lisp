(load "/home/lain/quicklisp/setup.lisp")
(load "modules/yggdrasil.lisp")
(load "modules/ipfs.lisp")
(ql:quickload :hunchentoot :silent t)

(hunchentoot:define-easy-handler (status :uri "/status") ()
  (setf (hunchentoot:content-type*) "text/plain; charset=utf-8")
  (format nil "~%((yggdrasil ~A)~% (ipfs ~A))~%"
    (singularity.modules.yggdrasil:get-ipv6)
    (singularity.modules.ipfs:get-peer-id)))

(format t "~A [HTTP] Server starting...~%" (get-universal-time))
(defvar *acceptor* (hunchentoot:start (make-instance 'hunchentoot:easy-acceptor :port 49406 :address "::")))
(format t "~A [HTTP] Server started on port 49406~%" (get-universal-time))

(loop (sleep 100))
