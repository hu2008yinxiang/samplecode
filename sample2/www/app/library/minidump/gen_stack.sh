#!/bin/sh
curDir=$(dirname $(readlink -f $0))
export PATH=$curDir:$PATH
tmp="tmp"
so_file=$1
dump_file=$2
if [ ! -f $so_file ]
then
	echo "no so file named $so_file !"
	exit
fi
if [ ! -f $dump_file ]
then
	echo "no dump _file named $dump_file !"
	exit
fi

dump_syms_cmd="dump_syms $so_file 2>/dev/null > $tmp/tmp.sym"
mkdir $tmp 2>/dev/null
echo $so_file
echo "$dump_syms_cmd"
sh -c "$dump_syms_cmd"
line=`head -1 $tmp/tmp.sym`
index=1
for seg in $line
do

case $index in
1) so_type=$seg;;
2) os_type=$seg;;
3) arc_type=$seg;;
4) sym_sign=$seg;;
5) so_name=$seg;;
esac 
index=`expr $index + 1`
done
echo $line
sym_dir=$tmp/symbol
sym_file=$sym_dir/$so_name/$sym_sign/$so_name.sym
mkdir -p `dirname $sym_file`
mv $tmp/tmp.sym $sym_file
echo $sym_file
walk_stack_cmd="minidump_stackwalk $dump_file $sym_dir 2>/dev/null | grep '!' > $tmp/tmp.stack"
echo $walk_stack_cmd
sh -c "$walk_stack_cmd"
cat $tmp/tmp.stack
#rm -rf $tmp
